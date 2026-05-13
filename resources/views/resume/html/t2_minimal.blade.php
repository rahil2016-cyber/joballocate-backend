<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; font-size: 1em; color: #1a1a1a; background: transparent; }
        .wrap { margin: 0; padding: 4mm 2mm 6mm; background: #fff; }
        h1 { font-size: 1.55rem; font-weight: 700; margin: 0 0 4px; letter-spacing: -0.5px; }
        .sub { color: #666; margin-bottom: 24px; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #888; margin: 28px 0 10px; border-top: 1px solid #eee; padding-top: 20px; }
        h2:first-of-type { border-top: 0; padding-top: 0; }
        p { line-height: 1.55; margin: 0 0 8px; }
        ul { margin: 0; padding-left: 18px; }
        .row { margin-bottom: 14px; }
        .row b { display: block; }
        .meta { color: #777; font-size: 12px; }
    </style>
</head>
<body>
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $workList = $resume['work_experience'] ?? [];
    $internList = $resume['internships'] ?? [];
    $hasExp = $F::hasExperienceBlocks($workList) || $F::hasExperienceBlocks($internList);
    $hasProj = $F::hasExperienceBlocks($resume['projects'] ?? []);
    $metaBits = [];
    foreach ([$resume['mobile'] ?? '', $resume['email'] ?? '', $resume['location'] ?? ''] as $bit) {
        if ($F::filled($bit)) {
            $metaBits[] = $bit;
        }
    }
@endphp
<div class="a4-doc">
<div class="wrap">
    @if($F::filled($resume['full_name'] ?? null))
        <h1>{{ e($resume['full_name']) }}</h1>
    @endif
    @if($F::filled($resume['professional_title'] ?? null))
        <div class="sub">{{ e($resume['professional_title']) }}</div>
    @endif
    @if($metaBits !== [])
        <p class="meta">{{ e(implode(' · ', $metaBits)) }}</p>
    @endif
    @if($summaryPlain !== '')
        <h2>Summary</h2>
        <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
    @endif
    @if($skillsShow !== [])
        <h2>Skills</h2>
        <p>{{ e(implode(' · ', $skillsShow)) }}</p>
    @endif
    @if($hasEdu)
        <h2>Education</h2>
        @php $listedEdu = false; @endphp
        @foreach($resume['education_list'] ?? [] as $ed)
            @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                @php $listedEdu = true; @endphp
                <div class="row"><b>{{ e($ed['title'] ?? '') }}</b>{{ e($ed['institution'] ?? '') }} — {{ e($ed['year'] ?? '') }} {{ e($ed['marks'] ?? '') }}</div>
            @endif
        @endforeach
        @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
            @php
                $gc = $F::filled($resume['graduation']['course'] ?? null) ? (string) $resume['graduation']['course'] : '';
                $gcol = $F::filled($resume['graduation']['college'] ?? null) ? (string) $resume['graduation']['college'] : '';
                $gLine = ($gc !== '' && $gcol !== '') ? $gc.', '.$gcol : ($gc !== '' ? $gc : $gcol);
            @endphp
            <p>{{ e($gLine) }}</p>
        @endif
    @endif
    @if($hasExp)
        <h2>Experience</h2>
        @foreach(array_merge($workList, $internList) as $x)
            @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                <div class="row"><b>{{ e($x['heading'] ?? '') }}</b><span class="meta">{{ e($x['dates'] ?? '') }}</span>
                    @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                </div>
            @endif
        @endforeach
    @endif
    @if($hasProj)
        <h2>Projects</h2>
        @foreach($resume['projects'] ?? [] as $x)
            @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                <div class="row"><b>{{ e($x['heading'] ?? '') }}</b><span class="meta">{{ e($x['dates'] ?? '') }}</span>
                    @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                </div>
            @endif
        @endforeach
    @endif
</div>
</div>
</body>
</html>
