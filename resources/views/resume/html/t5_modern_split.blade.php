<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; }
        .hero { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 28px 22px 36px; color: #fff; }
        .hero h1 { margin: 0; font-size: 1.55rem; font-weight: 800; }
        .hero p { margin: 8px 0 0; opacity: .95; font-size: 0.9rem; }
        .t5-columns { margin: 0; background: #1e293b; border-radius: 0; padding: 22px 18px 28px; display: grid; grid-template-columns: 38% 1fr; gap: 18px; width: 100%; max-width: 100%; }
        .card { background: #334155; border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .card h3 { margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .12em; color: #a5b4fc; }
        ul { margin: 0; padding-left: 16px; font-size: 13px; }
        li { margin-bottom: 4px; }
        h2 { font-size: 13px; color: #c7d2fe; text-transform: uppercase; letter-spacing: .1em; margin: 0 0 12px; }
        .exp { margin-bottom: 16px; font-size: 13px; }
        .exp strong { color: #fff; font-size: 14px; }
        .exp em { color: #94a3b8; font-style: normal; font-size: 12px; }
    </style>
</head>
<body class="a4-body--dark">
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $langsShow = $F::nonEmptyStrings($resume['languages'] ?? []);
    $certsShow = $F::nonEmptyStrings($resume['certifications'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $hasProj = $F::hasExperienceBlocks($resume['projects'] ?? []);
    $heroLine1 = array_filter([$resume['professional_title'] ?? '', $resume['location'] ?? ''], fn ($s) => $F::filled($s));
    $heroLine2 = array_filter([$resume['mobile'] ?? '', $resume['email'] ?? ''], fn ($s) => $F::filled($s));
@endphp
<div class="a4-doc a4-doc--dark">
<div class="hero">
    @if($F::filled($resume['full_name'] ?? null))
        <h1>{{ e($resume['full_name']) }}</h1>
    @endif
    @if($heroLine1 !== [])
        <p>{{ e(implode(' · ', $heroLine1)) }}</p>
    @endif
    @if($heroLine2 !== [])
        <p>{{ e(implode(' · ', $heroLine2)) }}</p>
    @endif
</div>
<div class="t5-columns">
    <aside>
        @if($skillsShow !== [])
            <div class="card"><h3>Skills</h3><ul>@foreach($skillsShow as $s)<li>{{ e($s) }}</li>@endforeach</ul></div>
        @endif
        @if($langsShow !== [])
            <div class="card"><h3>Languages</h3><ul>@foreach($langsShow as $l)<li>{{ e($l) }}</li>@endforeach</ul></div>
        @endif
        @if($certsShow !== [])
            <div class="card"><h3>Certifications</h3><ul>@foreach($certsShow as $c)<li>{{ e($c) }}</li>@endforeach</ul></div>
        @endif
    </aside>
    <main>
        @if($summaryPlain !== '')
            <h2>Summary</h2>
            <p style="font-size:14px;line-height:1.6">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
        @endif
        @if($hasEdu)
            <h2>Education</h2>
            @php $listedEdu = false; @endphp
            @foreach($resume['education_list'] ?? [] as $ed)
                @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                    @php $listedEdu = true; @endphp
                    <div class="exp"><strong>{{ e($ed['title'] ?? '') }}</strong><br><em>{{ e($ed['institution'] ?? '') }} · {{ e($ed['year'] ?? '') }}</em></div>
                @endif
            @endforeach
            @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                <div class="exp"><strong>{{ e($resume['graduation']['course'] ?? '') }}</strong><br><em>{{ e($resume['graduation']['college'] ?? '') }}</em></div>
            @endif
        @endif
        @if($hasWork)
            <h2>Work</h2>
            @foreach($resume['work_experience'] ?? [] as $x)
                @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                    <div class="exp"><strong>{{ e($x['heading'] ?? '') }}</strong> <em>{{ e($x['dates'] ?? '') }}</em>
                        @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($hasProj)
            <h2>Projects</h2>
            @foreach($resume['projects'] ?? [] as $x)
                @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                    <div class="exp"><strong>{{ e($x['heading'] ?? '') }}</strong> <em>{{ e($x['dates'] ?? '') }}</em>
                        @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
    </main>
</div>
</div>
</body>
</html>
