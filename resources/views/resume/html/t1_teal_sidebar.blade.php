<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 1em; color: #222; background: transparent; }
        .page { margin: 0; background: #fff; display: flex; }
        .side { width: 32%; background: #0d7377; color: #fff; padding: 28px 20px; }
        .main { flex: 1; padding: 28px 26px; }
        h1 { margin: 0 0 6px; font-size: 1.35rem; color: #0d7377; font-weight: 800; }
        .tag { color: #555; font-size: 0.85rem; margin-bottom: 18px; }
        h2 { font-size: 0.68rem; letter-spacing: 1.2px; text-transform: uppercase; color: #0d7377; border-bottom: 2px solid #0d7377; padding-bottom: 4px; margin: 18px 0 10px; }
        .side h2 { color: #b8fff5; border-color: rgba(255,255,255,.35); }
        ul { margin: 0; padding-left: 18px; }
        li { margin-bottom: 4px; }
        .muted { color: #666; font-size: 12px; }
        .block { margin-bottom: 12px; }
        .block strong { display: block; color: #0d7377; }
        .photo { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #b8fff5; display: block; margin: 0 auto 16px; }
        table.edu { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.edu th, table.edu td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        table.edu th { background: #e8f7f7; color: #0d7377; }
    </style>
</head>
<body>
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
    $contactAny = $F::filled($resume['mobile'] ?? null) || $F::filled($resume['email'] ?? null);
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
@endphp
<div class="a4-doc">
<div class="page">
    <aside class="side">
        @if(!empty($resume['photo_url']))
            <img class="photo" src="{{ e($resume['photo_url']) }}" alt="">
        @endif
        @if($contactAny)
            <h2>Get in touch</h2>
            @if($F::filled($resume['mobile'] ?? null))
                <p><strong>Mobile</strong><br>{{ e($resume['mobile']) }}</p>
            @endif
            @if($F::filled($resume['email'] ?? null))
                <p><strong>Email</strong><br>{{ e($resume['email']) }}</p>
            @endif
        @endif
        @if($skillsShow !== [])
            <h2>Skills</h2>
            <ul>@foreach($skillsShow as $s)<li>{{ e($s) }}</li>@endforeach</ul>
        @endif
        @if($langsShow !== [])
            <h2>Languages</h2>
            <ul>@foreach($langsShow as $l)<li>{{ e($l) }}</li>@endforeach</ul>
        @endif
        @if($certsShow !== [])
            <h2>Certifications</h2>
            <ul>@foreach($certsShow as $c)<li>{{ e($c) }}</li>@endforeach</ul>
        @endif
    </aside>
    <section class="main">
        @if($F::filled($resume['full_name'] ?? null))
            <h1>{{ e($resume['full_name']) }}</h1>
        @endif
        @if($F::filled($resume['professional_title'] ?? null))
            <div class="tag">{{ e($resume['professional_title']) }}</div>
        @endif
        @if($summaryPlain !== '')
            <h2>Resume summary</h2>
            <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
        @endif
        @if($pdRows !== [] || $showIndiaRow)
            <h2>Personal details</h2>
            <table class="edu">
                @foreach($pdRows as $label => $val)
                    <tr><th>{{ e($label) }}</th><td>{{ e($val) }}</td></tr>
                @endforeach
                @if($showIndiaRow)
                    <tr><th>Residing in India</th><td>{{ !empty($resume['residing_in_india']) ? 'Yes' : 'No' }}</td></tr>
                @endif
            </table>
        @endif
        @if($hasEdu)
            <h2>Education</h2>
            @php $listedEdu = false; @endphp
            @foreach($resume['education_list'] ?? [] as $ed)
                @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                    @php $listedEdu = true; @endphp
                    <div class="block"><strong>{{ e($ed['title'] ?? '') }}</strong>
                        <span class="muted">{{ e($ed['institution'] ?? '') }}</span><br>
                        {{ e($ed['year'] ?? '') }} @if($F::filled($ed['marks'] ?? null)) · {{ e($ed['marks']) }} @endif
                        @if($F::filled($ed['mode'] ?? null)) · {{ e($ed['mode']) }} @endif
                    </div>
                @endif
            @endforeach
            @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                <p class="muted">{{ e($resume['graduation']['course'] ?? '') }} @ {{ e($resume['graduation']['college'] ?? '') }}</p>
            @endif
        @endif
        @if($hasIntern)
            <h2>Internships</h2>
            @foreach($resume['internships'] ?? [] as $x)
                @if($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null))
                    <div class="block"><strong>{{ e($x['heading'] ?? '') }}</strong> <span class="muted">{{ e($x['dates'] ?? '') }}</span>
                        @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($hasProj)
            <h2>Projects</h2>
            @foreach($resume['projects'] ?? [] as $x)
                @if($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null))
                    <div class="block"><strong>{{ e($x['heading'] ?? '') }}</strong> <span class="muted">{{ e($x['dates'] ?? '') }}</span>
                        @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if($hasWork)
            <h2>Work experience</h2>
            @foreach($resume['work_experience'] ?? [] as $x)
                @if($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null))
                    <div class="block"><strong>{{ e($x['heading'] ?? '') }}</strong> <span class="muted">{{ e($x['dates'] ?? '') }}</span>
                        @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
                    </div>
                @endif
            @endforeach
        @endif
        @if(!empty($resume['academic_achievements']))
            @php $ach = array_values(array_filter($resume['academic_achievements'], fn ($a) => $F::filled(is_string($a) ? $a : null))); @endphp
            @if($ach !== [])
                <h2>Academic achievements</h2>
                <ul>@foreach($ach as $a)<li>{{ e($a) }}</li>@endforeach</ul>
            @endif
        @endif
    </section>
</div>
</div>
</body>
</html>
