<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Helvetica, Arial, sans-serif; background: transparent; color: #000; }
        .t11 { padding: 8mm 9mm 10mm !important; }
        .t11-head { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #000; padding-bottom: 4mm; margin-bottom: 6mm; }
        .t11-head h1 { margin: 0; font-size: 24pt; font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
        .t11-head .sub { font-size: 10pt; font-weight: 500; text-align: right; max-width: 45%; }
        .t11-contact { font-size: 8.5pt; margin-bottom: 6mm; display: flex; flex-wrap: wrap; gap: 4px 14px; text-transform: uppercase; letter-spacing: 0.08em; }
        .rc-h2 { font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.2em; margin: 14px 0 8px; border-top: 1px solid #000; padding-top: 6px; }
        .rc-h2:first-child { margin-top: 0; border-top: none; padding-top: 0; }
        .rc-block { margin-bottom: 10px; }
        .rc-block strong { font-size: 10pt; }
        .rc-muted { font-size: 8.5pt; color: #404040; }
        .rc-ul { margin: 0; padding-left: 14px; font-size: 9pt; }
        .rc-summary { font-size: 9.5pt; line-height: 1.55; margin: 0 0 8px; max-width: 95%; }
        .rc-table { width: 100%; font-size: 9pt; border-collapse: collapse; }
        .rc-table th, .rc-table td { border-top: 1px solid #e5e5e5; padding: 5px 0; text-align: left; vertical-align: top; }
        .rc-table th { width: 32%; font-weight: 700; text-transform: uppercase; font-size: 7.5pt; letter-spacing: 0.06em; }
        .t11-skills { font-size: 9pt; line-height: 1.6; }
    </style>
</head>
<body>
@php extract(\App\Support\ResumeHtmlViewComposer::data($resume), EXTR_SKIP); @endphp
<div class="a4-doc t11">
    <header class="t11-head">
        <div>
            @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
        </div>
        @if($F::filled($resume['professional_title'] ?? null))
            <p class="sub">{{ e($resume['professional_title']) }}</p>
        @endif
    </header>
    @php $meta = array_filter([$resume['email'] ?? '', $resume['mobile'] ?? '', $resume['location'] ?? ''], fn ($s) => $F::filled($s)); @endphp
    @if($meta !== [])<div class="t11-contact">@foreach($meta as $m)<span>{{ e($m) }}</span>@endforeach</div>@endif
    @if($skillsShow !== [])
        <div class="t11-skills"><strong>Skills — </strong>{{ e(implode(' / ', array_slice($skillsShow, 0, 20))) }}</div>
    @endif
    @include('resume.html._main_blocks')
    @if($langsShow !== [] || $certsShow !== [])
        @if($langsShow !== [])
            <h2 class="rc-h2">Languages</h2>
            <p class="t11-skills">{{ e(implode(' · ', $langsShow)) }}</p>
        @endif
        @if($certsShow !== [])
            <h2 class="rc-h2">Certifications</h2>
            <ul class="rc-ul">@foreach($certsShow as $c)<li>{{ e($c) }}</li>@endforeach</ul>
        @endif
    @endif
</div>
</body>
</html>
