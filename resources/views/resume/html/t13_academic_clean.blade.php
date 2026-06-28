<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: transparent; color: #111; line-height: 1.4; }
        .t13 { padding: 10mm 12mm 10mm !important; }
        .t13-head { text-align: center; margin-bottom: 5mm; }
        .t13-head h1 { margin: 0 0 6px; font-size: 22pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #000; }
        .t13-contact { font-size: 9.5pt; color: #333; line-height: 1.5; }
        .t13-contact a { color: #0275d8; text-decoration: underline; }
        .rc-h2 { font-size: 11.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 6px; border-bottom: 1.5px solid #222; padding-bottom: 3px; }
        .rc-h2:first-of-type { margin-top: 0; }
        .rc-block { margin-bottom: 12px; }
        .rc-block-header { font-size: 9.5pt; margin-bottom: 3px; }
        .rc-block-header strong { font-size: 10pt; color: #000; }
        .rc-block-header .dates { color: #333; font-weight: 500; }
        .rc-ul { margin: 0 0 8px; padding-left: 16px; font-size: 9.5pt; list-style-type: disc; color: #222; }
        .rc-ul li { margin-bottom: 3px; }
        .rc-summary { font-size: 9.5pt; color: #222; margin: 0 0 10px; text-align: justify; }
        .rc-project-body { font-size: 9.5pt; margin: 0 0 6px; color: #222; text-align: justify; }
    </style>
</head>
<body>
@php extract(\App\Support\ResumeHtmlViewComposer::data($resume), EXTR_SKIP); @endphp
<div class="a4-doc t13">
    <header class="t13-head">
        @if($F::filled($resume['full_name'] ?? null))
            <h1>{{ e($resume['full_name']) }}</h1>
        @endif
        <div class="t13-contact">
            @php
                $personal = $resume['personal_details'] ?? [];
                $socialLinks = [];
                foreach ($personal as $p) {
                    $label = strtolower(trim($p['label'] ?? ''));
                    if (in_array($label, ['linkedin', 'github', 'portfolio', 'website'])) {
                        $socialLinks[] = [
                            'label' => $p['label'],
                            'value' => $p['value']
                        ];
                    }
                }

                $headerParts = [];
                if ($F::filled($resume['email'] ?? null)) {
                    $headerParts[] = e($resume['email']);
                }
                if ($F::filled($resume['mobile'] ?? null)) {
                    $headerParts[] = e($resume['mobile']);
                }
                if ($F::filled($resume['location'] ?? null)) {
                    $headerParts[] = e($resume['location']);
                }
                foreach ($socialLinks as $sl) {
                    $val = $sl['value'];
                    $url = $val;
                    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                        $url = 'https://' . $url;
                    }
                    $headerParts[] = '<a href="' . e($url) . '" target="_blank">' . e($val) . '</a>';
                }
            @endphp
            {!! implode(' | ', $headerParts) !!}
        </div>
    </header>

    @if($F::filled($resume['summary'] ?? null))
        <h2 class="rc-h2">Profile</h2>
        <p class="rc-summary">{{ e($resume['summary']) }}</p>
    @endif

    @if($hasEdu)
        <h2 class="rc-h2">Education</h2>
        <ul class="rc-ul">
            @foreach($resume['education_list'] as $ed)
                @php
                    $parts = array_filter([$ed['title'] ?? null, $ed['institution'] ?? null, $ed['marks'] ?? null, $ed['year'] ?? null], fn($s) => $F::filled($s));
                @endphp
                @if(count($parts) > 0)
                    <li>{{ implode(' | ', $parts) }}</li>
                @endif
            @endforeach
        </ul>
    @endif

    @if($skillsShow !== [])
        <h2 class="rc-h2">Skill</h2>
        <ul class="rc-ul">
            @foreach($skillsShow as $s)
                @if(strpos($s, ':') !== false)
                    @php
                        $parts = explode(':', $s, 2);
                    @endphp
                    <li><strong>{{ trim($parts[0]) }}:</strong> {{ trim($parts[1]) }}</li>
                @else
                    <li>{{ $s }}</li>
                @endif
            @endforeach
        </ul>
    @endif

    @if($hasIntern)
        <h2 class="rc-h2">Internship Experience</h2>
        @foreach($resume['internships'] as $x)
            <div class="rc-block">
                <div class="rc-block-header">
                    <strong>{{ e($x['heading']) }}</strong>
                    @if($F::filled($x['dates']))
                        <span class="dates">| {{ e($x['dates']) }}</span>
                    @endif
                </div>
                @if($F::filled($x['body']))
                    <ul class="rc-ul">
                        @foreach(explode("\n", $x['body']) as $bullet)
                            @if(trim($bullet) !== '')
                                <li>{{ ltrim(trim($bullet), '•-* ') }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif

    @if($hasWork)
        <h2 class="rc-h2">Work Experience</h2>
        @foreach($resume['work_experience'] as $x)
            <div class="rc-block">
                <div class="rc-block-header">
                    <strong>{{ e($x['heading']) }}</strong>
                    @if($F::filled($x['dates']))
                        <span class="dates">| {{ e($x['dates']) }}</span>
                    @endif
                </div>
                @if($F::filled($x['body']))
                    <ul class="rc-ul">
                        @foreach(explode("\n", $x['body']) as $bullet)
                            @if(trim($bullet) !== '')
                                <li>{{ ltrim(trim($bullet), '•-* ') }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    @endif

    @if($hasProj)
        <h2 class="rc-h2">Projects</h2>
        @foreach($resume['projects'] as $x)
            <div class="rc-block">
                <div class="rc-block-header">
                    <strong>{{ e($x['heading']) }}</strong>
                    @if($F::filled($x['dates']))
                        <span class="dates">| {{ e($x['dates']) }}</span>
                    @endif
                </div>
                @if($F::filled($x['body']))
                    <p class="rc-project-body">{{ e($x['body']) }}</p>
                @endif
            </div>
        @endforeach
    @endif

    @php
        $academic = $resume['academic_achievements'] ?? [];
        $awards = $resume['awards_honors'] ?? [];
        $exams = $resume['competitive_exam_results'] ?? [];
        $allAchievements = array_merge($academic, $awards, $exams);
    @endphp
    @if(count($allAchievements) > 0)
        <h2 class="rc-h2">Achievements</h2>
        <ul class="rc-ul">
            @foreach($allAchievements as $line)
                @if($F::filled($line))
                    <li>{{ e($line) }}</li>
                @endif
            @endforeach
        </ul>
    @endif
</div>
</body>
</html>
