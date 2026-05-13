<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        body { margin: 0; background: transparent; color: #111; font-family: Georgia, 'Times New Roman', serif; }
        .wrap { margin: 0; padding: 5mm 6mm; }
        h1 { font-size: 1.75rem; font-weight: normal; border-bottom: 1px solid #333; padding-bottom: 12px; margin: 0 0 8px; }
        .contact { font-size: 13px; font-style: italic; color: #444; margin-bottom: 28px; }
        h2 { font-size: 15px; font-variant: small-caps; letter-spacing: .15em; margin: 28px 0 8px; }
        p, li { font-size: 14px; line-height: 1.55; }
        ul { padding-left: 18px; }
    </style>
</head>
<body>
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $langsShow = $F::nonEmptyStrings($resume['languages'] ?? []);
    $certsShow = $F::nonEmptyStrings($resume['certifications'] ?? []);
    $contactBits = array_filter([
        $resume['professional_title'] ?? '',
        $resume['email'] ?? '',
        $resume['mobile'] ?? '',
        $resume['location'] ?? '',
    ], fn ($s) => $F::filled($s));
@endphp
<div class="a4-doc">
<div class="wrap">
    @if($F::filled($resume['full_name'] ?? null))
        <h1>{{ e($resume['full_name']) }}</h1>
    @endif
    @if($contactBits !== [])
        <div class="contact">{{ e(implode(' — ', $contactBits)) }}</div>
    @endif
    @if($summaryPlain !== '')
        <h2>Summary</h2>
        <p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
    @endif
    @if($hasEdu)
        <h2>Education</h2>
        <ul>
            @php $listedEdu = false; @endphp
            @foreach($resume['education_list'] ?? [] as $ed)
                @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                    @php $listedEdu = true; @endphp
                    <li>
                        <strong>{{ e($ed['title'] ?? '') }}</strong>, {{ e($ed['institution'] ?? '') }}, {{ e($ed['year'] ?? '') }}
                        @if($F::filled($ed['marks'] ?? null))
                            , {{ e($ed['marks']) }}
                        @endif
                    </li>
                @endif
            @endforeach
            @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                <li>{{ e($resume['graduation']['course'] ?? '') }}, {{ e($resume['graduation']['college'] ?? '') }}</li>
            @endif
        </ul>
    @endif
    @if($hasWork)
        <h2>Experience</h2>
        @foreach($resume['work_experience'] ?? [] as $x)
            @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                <p><strong>{{ e($x['heading'] ?? '') }}</strong> ({{ e($x['dates'] ?? '') }})</p>
                @if($F::filled($x['body'] ?? null))<p>{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>@endif
            @endif
        @endforeach
    @endif
    @if($langsShow !== [] || $certsShow !== [])
        <h2>Languages & certifications</h2>
        <p>
            @if($langsShow !== [])
                {{ e(implode('; ', $langsShow)) }}
            @endif
            @if($langsShow !== [] && $certsShow !== [])
                <br>
            @endif
            @if($certsShow !== [])
                {{ e(implode('; ', $certsShow)) }}
            @endif
        </p>
    @endif
</div>
</div>
</body>
</html>
