<?php

namespace App\Support;

use App\Models\JobSeekerProfile;
use App\Models\User;

/**
 * Normalizes Flutter `resume_model_v1` envelope (+ profile) for HTML resume templates.
 *
 * @phpstan-type Row array{label: string, value: string}
 */
final class ResumeViewData
{
    /**
     * @param  array<string, mixed>|null  $envelope  `['schema' => 'resume_model_v1', 'data' => [...]]` or raw data map
     * @return array<string, mixed>
     */
    public static function fromEnvelope(?array $envelope, User $user, ?JobSeekerProfile $profile): array
    {
        $data = [];
        if (is_array($envelope)) {
            if (($envelope['schema'] ?? null) === 'resume_model_v1' && is_array($envelope['data'] ?? null)) {
                $data = $envelope['data'];
            } elseif (isset($envelope['full_name']) || isset($envelope['summary'])) {
                $data = $envelope;
            }
        }

        $fullName = self::str($data['full_name'] ?? null) ?: (string) $user->name;
        $title = self::str($data['professional_title'] ?? null);
        if ($title === '' && $profile !== null) {
            $title = (string) ($profile->headline ?? '');
        }

        $contact = is_array($data['contact'] ?? null) ? $data['contact'] : [];
        $mobile = self::str($contact['mobile'] ?? null);
        $email = self::str($contact['email'] ?? null);
        if ($mobile === '' && $user->phone) {
            $mobile = (string) $user->phone;
        }
        if ($email === '' && $user->email && ! Identifier::isSyntheticEmail((string) $user->email)) {
            $email = (string) $user->email;
        }

        $summary = self::str($data['summary'] ?? null);
        if ($summary === '' && $profile !== null) {
            $summary = (string) ($profile->bio ?? '');
        }

        $skills = self::stringList($data['skills'] ?? null);
        if ($skills === [] && $profile !== null && is_array($profile->skills)) {
            $skills = array_values(array_filter(array_map('strval', $profile->skills)));
        }

        $languages = self::stringList($data['languages'] ?? null);
        $certs = self::stringList($data['certifications'] ?? null);

        if ($certs === [] && is_array($profile?->certifications_structured)) {
            foreach ($profile->certifications_structured as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $n = self::str($row['name'] ?? null);
                $d = self::str($row['date'] ?? null);
                if ($n !== '') {
                    $certs[] = $d !== '' ? $n.' — '.$d : $n;
                }
            }
        }

        if ($languages === [] && is_array($profile?->languages_known)) {
            foreach ($profile->languages_known as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lang = self::str($row['language'] ?? null);
                $prof = self::str($row['proficiency'] ?? null);
                if ($lang !== '') {
                    $languages[] = $prof !== '' ? $lang.' — '.$prof : $lang;
                }
            }
        }

        $personalRows = self::personalRows($data['personal_details'] ?? null);
        $location = self::rowValue($personalRows, 'Current Location');
        if ($location === '' && $profile !== null) {
            $parts = array_filter([(string) ($profile->city ?? ''), (string) ($profile->country ?? '')]);
            $location = implode(', ', $parts);
        }

        $dob = self::rowValue($personalRows, 'Date of Birth');
        if ($dob === '' && $profile?->date_of_birth) {
            $dob = $profile->date_of_birth->format('d / m / Y');
        }
        $gender = self::rowValue($personalRows, 'Gender');
        if ($gender === '' && $profile?->gender) {
            $gender = (string) $profile->gender;
        }

        $hometown = self::rowValue($personalRows, 'Home Town');
        if ($hometown === '' && $profile?->hometown) {
            $hometown = (string) $profile->hometown;
        }

        $photo = self::str($data['profile_image_url'] ?? null);
        if ($photo === '' && $profile?->profile_photo_url) {
            $photo = (string) $profile->profile_photo_url;
        }

        $education = is_array($data['education'] ?? null) ? $data['education'] : [];
        $grad = is_array($education['graduation'] ?? null) ? $education['graduation'] : [];
        $school = is_array($education['schooling'] ?? null) ? $education['schooling'] : [];
        $c12 = is_array($school['class_12'] ?? null) ? $school['class_12'] : [];
        $c10 = is_array($school['class_10'] ?? null) ? $school['class_10'] : [];

        $educationList = [];
        if (is_array($data['education_entries'] ?? null)) {
            foreach ($data['education_entries'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $educationList[] = [
                    'title' => self::str($row['title'] ?? null),
                    'institution' => self::str($row['institution'] ?? null),
                    'year' => self::str($row['year_completed'] ?? $row['year'] ?? null),
                    'marks' => self::str($row['marks_or_grade'] ?? $row['marks'] ?? null),
                    'mode' => self::str($row['study_mode'] ?? $row['mode'] ?? null),
                ];
            }
        }

        if ($educationList === [] && is_array($profile?->education)) {
            foreach ($profile->education as $e) {
                if (! is_array($e)) {
                    continue;
                }
                $educationList[] = [
                    'title' => self::str($e['title'] ?? null),
                    'institution' => self::str($e['institution'] ?? null),
                    'year' => self::str($e['year_completed'] ?? null),
                    'marks' => self::str($e['marks_or_grade'] ?? null),
                    'mode' => self::str($e['study_mode'] ?? null),
                ];
            }
        }

        $internships = self::experienceBlocks($data['internships'] ?? null);
        $projects = self::experienceBlocks($data['projects'] ?? null);
        $work = self::experienceBlocks($data['work_experience'] ?? null);

        if ($work === [] && is_array($profile?->work_experience)) {
            $work = self::experienceBlocks($profile->work_experience);
        }

        $extras = [];
        if (is_array($data['extra_sections'] ?? null)) {
            foreach ($data['extra_sections'] as $block) {
                if (! is_array($block)) {
                    continue;
                }
                $extras[] = [
                    'title' => self::str($block['title'] ?? null),
                    'lines' => self::stringList($block['lines'] ?? null),
                ];
            }
        }

        $academic = self::academicLines($data['academic_achievements'] ?? null, $profile?->academic_achievements);
        $awards = self::awardLines($data['awards_honors'] ?? null, $profile?->awards_honors);
        $exams = self::examLines($data['competitive_exam_results'] ?? null, $profile?->competitive_exam_results);

        return [
            'full_name' => $fullName,
            'professional_title' => $title,
            'summary' => $summary,
            'mobile' => $mobile,
            'email' => $email,
            'photo_url' => $photo,
            'location' => $location,
            'hometown' => $hometown,
            'dob' => $dob,
            'gender' => $gender,
            'residing_in_india' => $profile?->residing_in_india ?? true,
            'highest_qualification' => (string) ($profile?->highest_qualification ?? ''),
            'skills' => $skills,
            'languages' => $languages,
            'certifications' => $certs,
            'graduation' => [
                'course' => self::str($grad['course'] ?? null),
                'college' => self::str($grad['college'] ?? null),
                'score' => self::str($grad['score'] ?? null),
            ],
            'class_12' => [
                'board' => self::str($c12['board_name'] ?? null),
                'medium' => self::str($c12['medium'] ?? null),
                'year' => self::str($c12['year_of_passing'] ?? null),
                'score' => self::str($c12['score'] ?? null),
            ],
            'class_10' => [
                'board' => self::str($c10['board_name'] ?? null),
                'medium' => self::str($c10['medium'] ?? null),
                'year' => self::str($c10['year_of_passing'] ?? null),
                'score' => self::str($c10['score'] ?? null),
            ],
            'education_list' => $educationList,
            'internships' => $internships,
            'projects' => $projects,
            'work_experience' => $work,
            'extra_sections' => $extras,
            'academic_achievements' => $academic,
            'awards_honors' => $awards,
            'competitive_exam_results' => $exams,
        ];
    }

    /**
     * @param  array<int, mixed>|null  $rows
     * @return list<Row>
     */
    private static function personalRows(?array $rows): array
    {
        $out = [];
        if (! is_array($rows)) {
            return $out;
        }
        foreach ($rows as $r) {
            if (! is_array($r)) {
                continue;
            }
            $out[] = [
                'label' => self::str($r['label'] ?? null),
                'value' => self::str($r['value'] ?? null),
            ];
        }

        return $out;
    }

    /**
     * @param  list<Row>  $rows
     */
    private static function rowValue(array $rows, string $wantLabel): string
    {
        $w = mb_strtolower(trim($wantLabel));
        foreach ($rows as $r) {
            if (mb_strtolower(trim($r['label'])) === $w) {
                return trim($r['value']);
            }
        }

        return '';
    }

    /**
     * @param  array<int, mixed>|null  $list
     * @return list<array{heading: string, dates: string, body: string}>
     */
    private static function experienceBlocks(?array $list): array
    {
        $out = [];
        if (! is_array($list)) {
            return $out;
        }
        foreach ($list as $item) {
            if (! is_array($item)) {
                continue;
            }
            $heading = self::str($item['company_name'] ?? $item['title'] ?? null);
            $dates = self::str($item['date_range'] ?? null);
            $bullets = self::stringList($item['bullets'] ?? null);
            $body = implode("\n", $bullets);
            if ($heading === '' && $dates === '' && $body === '') {
                continue;
            }
            $out[] = ['heading' => $heading, 'dates' => $dates, 'body' => $body];
        }

        return $out;
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    private static function stringList(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            $s = is_string($v) ? trim($v) : trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return $out;
    }

    private static function str(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_string($v)) {
            return trim($v);
        }

        return trim((string) $v);
    }

    /**
     * @param  mixed  $fromData
     * @param  mixed  $fromProfile
     * @return list<string>
     */
    private static function academicLines(mixed $fromData, mixed $fromProfile): array
    {
        $out = [];
        $src = is_array($fromData) && $fromData !== [] ? $fromData : $fromProfile;
        if (! is_array($src)) {
            return $out;
        }
        foreach ($src as $row) {
            if (is_string($row)) {
                $t = trim($row);
                if ($t !== '') {
                    $out[] = $t;
                }

                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $title = self::str($row['title'] ?? null);
            $detail = self::str($row['detail'] ?? null);
            if ($title === '' && $detail === '') {
                continue;
            }
            $out[] = $detail !== '' ? $title.': '.$detail : $title;
        }

        return $out;
    }

    /**
     * @param  mixed  $fromData
     * @param  mixed  $fromProfile
     * @return list<string>
     */
    private static function awardLines(mixed $fromData, mixed $fromProfile): array
    {
        $out = [];
        $src = is_array($fromData) && $fromData !== [] ? $fromData : $fromProfile;
        if (! is_array($src)) {
            return $out;
        }
        foreach ($src as $row) {
            if (is_string($row)) {
                $t = trim($row);
                if ($t !== '') {
                    $out[] = $t;
                }

                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $title = self::str($row['title'] ?? null);
            $detail = self::str($row['detail'] ?? null);
            if ($title === '' && $detail === '') {
                continue;
            }
            $out[] = $detail !== '' ? $title.' — '.$detail : $title;
        }

        return $out;
    }

    /**
     * @param  mixed  $fromData
     * @param  mixed  $fromProfile
     * @return list<string>
     */
    private static function examLines(mixed $fromData, mixed $fromProfile): array
    {
        $out = [];
        $src = is_array($fromData) && $fromData !== [] ? $fromData : $fromProfile;
        if (! is_array($src)) {
            return $out;
        }
        foreach ($src as $row) {
            if (is_string($row)) {
                $t = trim($row);
                if ($t !== '') {
                    $out[] = $t;
                }

                continue;
            }
            if (! is_array($row)) {
                continue;
            }
            $exam = self::str($row['exam'] ?? null);
            $result = self::str($row['result'] ?? null);
            if ($exam === '' && $result === '') {
                continue;
            }
            $out[] = $result !== '' ? $exam.': '.$result : $exam;
        }

        return $out;
    }
}
