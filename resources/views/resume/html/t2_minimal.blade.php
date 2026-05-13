<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; font-size: 13px; color: #1e293b; background: transparent; }
        .banner {
            background: linear-gradient(125deg, #0f172a 0%, #1e293b 42%, #334155 100%);
            color: #f8fafc;
            padding: 5mm 6mm 4.5mm;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }
        .banner h1 { margin: 0; font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; }
        .banner .tag { margin: 4px 0 0; font-size: 0.92rem; color: #94a3b8; font-weight: 500; }
        .chiprow { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 12px; font-size: 11px; color: #cbd5e1; text-transform: uppercase; letter-spacing: .06em; }
        .chiprow span { padding-bottom: 2px; border-bottom: 1px solid rgba(148, 163, 184, 0.45); }
        .headshot { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(248, 250, 252, 0.35); flex-shrink: 0; }
        .wrap { padding: 5mm 6mm 8mm; background: #fff; }
        h2 {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .16em;
            color: #64748b;
            margin: 20px 0 10px;
            border-left: 4px solid #0f172a;
            padding-left: 10px;
            font-weight: 800;
        }
        .skill {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11.5px;
            margin: 3px 4px 3px 0;
            font-weight: 500;
        }
        .row { margin-bottom: 12px; padding-left: 10px; border-left: 3px solid #e2e8f0; }
        .row strong { display: block; color: #0f172a; font-size: 14px; font-weight: 700; }
        .meta { color: #64748b; font-size: 11.5px; margin-top: 2px; }
        p.ex { margin: 4px 0 0; line-height: 1.55; font-size: 12.5px; }
        ul { margin: 0; padding-left: 16px; }
        ul li { margin-bottom: 4px; font-size: 12px; }
        .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; font-weight: 700; }
    </style>
</head>
<body>
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $langsShow = $F::nonEmptyStrings($resume['languages'] ?? []);
    $certsShow = $F::nonEmptyStrings($resume['certifications'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $hasIntern = $F::hasExperienceBlocks($resume['internships'] ?? []);
    $hasProj = $F::hasExperienceBlocks($resume['projects'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $awards = is_array($resume['awards_honors'] ?? null) ? $resume['awards_honors'] : [];
    $exams = is_array($resume['competitive_exam_results'] ?? null) ? $resume['competitive_exam_results'] : [];
    $academic = is_array($resume['academic_achievements'] ?? null) ? $resume['academic_achievements'] : [];
    $chips = [];
    foreach (['mobile', 'email', 'location'] as $k) {
        if ($F::filled($resume[$k] ?? null)) {
            $chips[] = $resume[$k];
        }
    }
@endphp
<div class="a4-doc">
    <div class="banner">
        <div style="min-width:0;">
            @if($F::filled($resume['full_name'] ?? null))
                <h1>{{ e($resume['full_name']) }}</h1>
            @endif
            @if($F::filled($resume['professional_title'] ?? null))
                <div class="tag">{{ e($resume['professional_title']) }}</div>
            @endif
            @if($chips !== [])
                <div class="chiprow">
                    @foreach($chips as $c)<span>{{ e($c) }}</span>@endforeach
                </div>
            @endif
        </div>
        @if($F::filled($resume['photo_url'] ?? null))
            <img class="headshot" src="{{ e($resume['photo_url']) }}" alt="">
        @endif
    </div>
    <div class="wrap">
        @if($summaryPlain !== '')
            <h2>Profile</h2>
            <p class="ex">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
        @endif
        @if($skillsShow !== [])
            <h2>Core skills</h2>
            <div>@foreach($skillsShow as $s)<span class="skill">{{ e($s) }}</span>@endforeach</div>
        @endif
        @if($langsShow !== [] || $certsShow !== [])
            <h2>Languages & certifications</h2>
            @if($langsShow !== [])
                <p class="ex" style="margin-bottom:8px"><span class="lbl">Languages</span><br>{{ e(implode(' · ', $langsShow)) }}</p>
            @endif
            @if($certsShow !== [])
                <p class="ex"><span class="lbl">Certifications</span><br>{{ e(implode(' · ', $certsShow)) }}</p>
            @endif
        @endif
        @if($hasEdu)
            <h2>Education</h2>
            @php $listedEdu = false; @endphp
            @foreach($resume['education_list'] ?? [] as $ed)
                @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                    @php $listedEdu = true; @endphp
                    <div class="row"><strong>{{ e($ed['title'] ?? '') }}</strong>
                        <div class="meta">{{ e($ed['institution'] ?? '') }}@if($F::filled($ed['year'] ?? null)) · {{ e($ed['year']) }}@endif @if($F::filled($ed['marks'] ?? null)) · {{ e($ed['marks']) }}@endif</div>
                    </div>
                @endif
            @endforeach
            @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                <div class="row"><strong>{{ e($resume['graduation']['course'] ?? '') }}</strong>
                    <div class="meta">{{ e($resume['graduation']['college'] ?? '') }}</div>
                </div>
            @endif
        @endif
        @if($hasWork)
            <h2>Work experience</h2>
            @foreach($resume['work_experience'] ?? [] as $x)
                @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                    <div class="row"><strong>{{ e($x['heading'] ?? '') }}</strong>
                        @if($F::filled($x['dates'] ?? null))<div class="meta">{{ e($x['dates']) }}</div>@endif
                        @if($F::filled($x['body'] ?? null))<p class="ex">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($hasIntern)
            <h2>Internships</h2>
            @foreach($resume['internships'] ?? [] as $x)
                @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                    <div class="row"><strong>{{ e($x['heading'] ?? '') }}</strong>
                        @if($F::filled($x['dates'] ?? null))<div class="meta">{{ e($x['dates']) }}</div>@endif
                        @if($F::filled($x['body'] ?? null))<p class="ex">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($hasProj)
            <h2>Projects</h2>
            @foreach($resume['projects'] ?? [] as $x)
                @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                    <div class="row"><strong>{{ e($x['heading'] ?? '') }}</strong>
                        @if($F::filled($x['dates'] ?? null))<div class="meta">{{ e($x['dates']) }}</div>@endif
                        @if($F::filled($x['body'] ?? null))<p class="ex">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($academic !== [])
            <h2>Academic highlights</h2>
            <ul>@foreach($academic as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
        @endif
        @if($awards !== [])
            <h2>Awards & honors</h2>
            <ul>@foreach($awards as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
        @endif
        @if($exams !== [])
            <h2>Competitive exams</h2>
            <ul>@foreach($exams as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
        @endif
    </div>
</div>
</body>
</html>
