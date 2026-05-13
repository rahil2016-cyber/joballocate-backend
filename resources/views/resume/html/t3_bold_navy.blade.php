<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        body { margin: 0; font-family: 'Georgia', serif; background: transparent; color: #0f172a; }
        .bar { height: 8px; background: linear-gradient(90deg, #1e3a5f, #3b82f6); }
        .wrap { margin: 0; background: #fff; padding: 4mm 5mm 6mm; }
        h1 { font-family: system-ui, sans-serif; font-size: 1.65rem; color: #1e3a5f; margin: 0; font-weight: 900; }
        .headline { font-family: system-ui, sans-serif; color: #3b82f6; font-weight: 600; margin: 6px 0 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; font-family: system-ui, sans-serif; font-size: 13px; }
        h2 { font-family: system-ui, sans-serif; font-size: 13px; color: #1e3a5f; text-transform: uppercase; letter-spacing: .08em; margin: 26px 0 10px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        .skill { display: inline-block; background: #e8eef9; padding: 4px 10px; border-radius: 20px; margin: 3px; font-size: 12px; font-family: system-ui, sans-serif; }
    </style>
</head>
<body>
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $mergedIp = array_merge($resume['internships'] ?? [], $resume['projects'] ?? []);
    $hasIp = $F::hasExperienceBlocks($mergedIp);
    $dobGender = trim(implode(' · ', array_filter([trim((string) ($resume['dob'] ?? '')), trim((string) ($resume['gender'] ?? ''))])));
@endphp
<div class="a4-doc">
<div class="bar"></div>
<div class="wrap">
    @if($F::filled($resume['full_name'] ?? null))
        <h1>{{ e($resume['full_name']) }}</h1>
    @endif
    @if($F::filled($resume['professional_title'] ?? null))
        <div class="headline">{{ e($resume['professional_title']) }}</div>
    @endif
    @if($F::filled($resume['mobile'] ?? null) || $F::filled($resume['email'] ?? null) || $F::filled($resume['location'] ?? null) || $dobGender !== '')
        <div class="grid">
            @if($F::filled($resume['mobile'] ?? null))
                <div><strong>Phone</strong><br>{{ e($resume['mobile']) }}</div>
            @endif
            @if($F::filled($resume['email'] ?? null))
                <div><strong>Email</strong><br>{{ e($resume['email']) }}</div>
            @endif
            @if($F::filled($resume['location'] ?? null))
                <div><strong>Location</strong><br>{{ e($resume['location']) }}</div>
            @endif
            @if($dobGender !== '')
                <div><strong>DOB / Gender</strong><br>{{ e($dobGender) }}</div>
            @endif
        </div>
    @endif
    @if($summaryPlain !== '')
        <h2>Profile</h2>
        <p style="font-size:15px;line-height:1.6">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
    @endif
    @if($skillsShow !== [])
        <h2>Core skills</h2>
        <div>@foreach($skillsShow as $s)<span class="skill">{{ e($s) }}</span>@endforeach</div>
    @endif
    @if($hasEdu)
        <h2>Education</h2>
        @php $listedEdu = false; @endphp
        @foreach($resume['education_list'] ?? [] as $ed)
            @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                @php $listedEdu = true; @endphp
                <p><strong>{{ e($ed['title'] ?? '') }}</strong> — {{ e($ed['institution'] ?? '') }} <em>({{ e($ed['year'] ?? '') }})</em></p>
            @endif
        @endforeach
        @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
            <p><strong>{{ e($resume['graduation']['course'] ?? '') }}</strong> — {{ e($resume['graduation']['college'] ?? '') }}</p>
        @endif
    @endif
    @if($hasWork)
        <h2>Work</h2>
        @foreach($resume['work_experience'] ?? [] as $x)
            @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                <p><strong>{{ e($x['heading'] ?? '') }}</strong> <em>{{ e($x['dates'] ?? '') }}</em>
                    @if($F::filled($x['body'] ?? null))<br>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}@endif
                </p>
            @endif
        @endforeach
    @endif
    @if($hasIp)
        <h2>Projects & internships</h2>
        @foreach($mergedIp as $x)
            @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                <p><strong>{{ e($x['heading'] ?? '') }}</strong> <em>{{ e($x['dates'] ?? '') }}</em>
                    @if($F::filled($x['body'] ?? null))<br>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}@endif
                </p>
            @endif
        @endforeach
    @endif
</div>
</div>
</body>
</html>
