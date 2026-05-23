@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $langsShow = $F::nonEmptyStrings($resume['languages'] ?? []);
    $certsShow = $F::nonEmptyStrings($resume['certifications'] ?? []);
    $hasIntern = $F::hasExperienceBlocks($resume['internships'] ?? []);
    $hasProj = $F::hasExperienceBlocks($resume['projects'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $contactAny = $F::filled($resume['mobile'] ?? null) || $F::filled($resume['email'] ?? null) || $F::filled($resume['location'] ?? null);
    $pdRows = [];
    foreach ([
        'Current location' => $resume['location'] ?? '',
        'Home town' => $resume['hometown'] ?? '',
        'Date of birth' => $resume['dob'] ?? '',
        'Gender' => $resume['gender'] ?? '',
    ] as $lbl => $val) {
        if ($F::filled($val)) {
            $pdRows[$lbl] = $val;
        }
    }
    $showIndiaRow = array_key_exists('residing_in_india', $resume);
    $photoUrl = $resume['photo_url'] ?? null;
    $initials = '';
    $nm = trim((string) ($resume['full_name'] ?? ''));
    if ($nm !== '') {
        $parts = preg_split('/\s+/', $nm);
        $a = $parts[0] ?? '';
        $b = $parts[count($parts) - 1] ?? '';
        $initials = strtoupper(substr($a, 0, 1).substr($b, 0, 1));
    }
@endphp
