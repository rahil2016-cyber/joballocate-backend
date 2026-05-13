<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, sans-serif; font-size: 12.5px; color: #292524; background: transparent; }
        .mast { padding: 5mm 7mm 4mm; background: #fff; }
        .mast h1 {
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: #0c0a09;
            letter-spacing: -0.02em;
        }
        .mast .tag { font-family: Georgia, serif; font-style: italic; color: #78716c; font-size: 1.05rem; margin-top: 6px; }
        .rule { height: 3px; background: linear-gradient(90deg, #9f1239 0%, #ea580c 100%); margin: 12px 0 8px; border-radius: 2px; max-width: 120px; }
        .contactline { font-size: 12px; color: #57534e; line-height: 1.5; }
        .page { display: grid; grid-template-columns: 1fr 31%; gap: 0; align-items: start; }
        main { padding: 2mm 7mm 8mm 8mm; background: #fff; }
        aside {
            background: linear-gradient(180deg, #fffbeb 0%, #ffedd5 55%, #fed7aa 100%);
            padding: 2mm 5mm 8mm 6mm;
            border-left: 4px solid #9f1239;
            min-height: 100%;
        }
        h2 {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .18em;
            color: #9f1239;
            margin: 20px 0 8px;
            font-weight: 700;
        }
        aside h2 { color: #9a3412; letter-spacing: .12em; font-size: 10px; }
        .aside-card { background: rgba(255, 255, 255, 0.82); border-radius: 10px; padding: 10px 10px 8px; margin-bottom: 10px; border: 1px solid #fdba74; }
        .skill {
            display: inline-block;
            background: #fff;
            border: 1px solid #fecaca;
            color: #7f1d1d;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            margin: 2px 3px 2px 0;
            font-weight: 600;
        }
        .exp { margin-bottom: 12px; }
        .exp strong { font-family: Georgia, serif; font-size: 14px; display: block; color: #0c0a09; }
        .exp em { color: #78716c; font-style: normal; font-size: 11.5px; }
        p.body { line-height: 1.55; margin: 4px 0 0; }
        ul { margin: 0; padding-left: 16px; }
        ul li { margin-bottom: 4px; }
        .photo { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #fecaca; display: block; margin: 0 auto 8px; }
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
    $contactBits = array_filter([
        $resume['mobile'] ?? '',
        $resume['email'] ?? '',
        $resume['location'] ?? '',
    ], fn ($s) => $F::filled($s));
@endphp
<div class="a4-doc">
    <div class="mast">
        @if($F::filled($resume['full_name'] ?? null))
            <h1>{{ e($resume['full_name']) }}</h1>
        @endif
        @if($F::filled($resume['professional_title'] ?? null))
            <div class="tag">{{ e($resume['professional_title']) }}</div>
        @endif
        <div class="rule"></div>
        @if($contactBits !== [])
            <div class="contactline">{{ e(implode(' · ', $contactBits)) }}</div>
        @endif
    </div>
    <div class="page">
        <main>
            @if($summaryPlain !== '')
                <h2>Summary</h2>
                <p class="body">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
            @endif
            @if($hasWork)
                <h2>Experience</h2>
                @foreach($resume['work_experience'] ?? [] as $x)
                    @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                        <div class="exp">
                            <strong>{{ e($x['heading'] ?? '') }}</strong>
                            @if($F::filled($x['dates'] ?? null))<em> {{ e($x['dates']) }}</em>@endif
                            @if($F::filled($x['body'] ?? null))<p class="body">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                        </div>
                    @endif
                @endforeach
            @endif
            @if($hasIntern)
                <h2>Internships</h2>
                @foreach($resume['internships'] ?? [] as $x)
                    @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                        <div class="exp">
                            <strong>{{ e($x['heading'] ?? '') }}</strong>
                            @if($F::filled($x['dates'] ?? null))<em> {{ e($x['dates']) }}</em>@endif
                            @if($F::filled($x['body'] ?? null))<p class="body">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                        </div>
                    @endif
                @endforeach
            @endif
            @if($hasProj)
                <h2>Projects</h2>
                @foreach($resume['projects'] ?? [] as $x)
                    @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                        <div class="exp">
                            <strong>{{ e($x['heading'] ?? '') }}</strong>
                            @if($F::filled($x['dates'] ?? null))<em> {{ e($x['dates']) }}</em>@endif
                            @if($F::filled($x['body'] ?? null))<p class="body">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                        </div>
                    @endif
                @endforeach
            @endif
            @if($academic !== [])
                <h2>Academic highlights</h2>
                <ul>@foreach($academic as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
            @endif
        </main>
        <aside>
            @if($F::filled($resume['photo_url'] ?? null))
                <div class="aside-card" style="text-align:center">
                    <img class="photo" src="{{ e($resume['photo_url']) }}" alt="">
                </div>
            @endif
            @if($skillsShow !== [])
                <h2>Skills</h2>
                <div class="aside-card">@foreach($skillsShow as $s)<span class="skill">{{ e($s) }}</span>@endforeach</div>
            @endif
            @if($langsShow !== [] || $certsShow !== [])
                <h2>Credentials</h2>
                <div class="aside-card">
                    @if($langsShow !== [])
                        <p style="margin:0 0 8px;font-size:11.5px"><strong style="color:#9a3412">Languages</strong><br>{{ e(implode(' · ', $langsShow)) }}</p>
                    @endif
                    @if($certsShow !== [])
                        <p style="margin:0;font-size:11.5px"><strong style="color:#9a3412">Certifications</strong><br>{{ e(implode(' · ', $certsShow)) }}</p>
                    @endif
                </div>
            @endif
            @if($hasEdu)
                <h2>Education</h2>
                <div class="aside-card">
                    @php $listedEdu = false; @endphp
                    @foreach($resume['education_list'] ?? [] as $ed)
                        @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                            @php $listedEdu = true; @endphp
                            <p style="margin:0 0 8px">
                                <strong style="font-family:Georgia,serif">{{ e($ed['title'] ?? '') }}</strong><br>
                                <span style="color:#78716c;font-size:11px">{{ e($ed['institution'] ?? '') }}@if($F::filled($ed['year'] ?? null)) · {{ e($ed['year']) }}@endif</span>
                            </p>
                        @endif
                    @endforeach
                    @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                        <p style="margin:0">
                            <strong style="font-family:Georgia,serif">{{ e($resume['graduation']['course'] ?? '') }}</strong><br>
                            <span style="color:#78716c;font-size:11px">{{ e($resume['graduation']['college'] ?? '') }}</span>
                        </p>
                    @endif
                </div>
            @endif
            @if($awards !== [])
                <h2>Awards</h2>
                <div class="aside-card">
                    <ul style="padding-left:14px;margin:0">@foreach($awards as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
                </div>
            @endif
            @if($exams !== [])
                <h2>Exams</h2>
                <div class="aside-card">
                    <ul style="padding-left:14px;margin:0">@foreach($exams as $a)@if($F::filled($a))<li>{{ e($a) }}</li>@endif @endforeach</ul>
                </div>
            @endif
        </aside>
    </div>
</div>
</body>
</html>
