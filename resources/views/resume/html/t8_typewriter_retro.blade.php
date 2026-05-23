<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Courier New', Courier, monospace; background: transparent; }
        .t8 { background: #f4e8d0 !important; color: #1c1917 !important; padding: 7mm 8mm 9mm !important; border: 2px double #78350f; }
        .t8-head { text-align: center; border-bottom: 1px dashed #78350f; padding-bottom: 5mm; margin-bottom: 5mm; }
        .t8-head h1 { margin: 0; font-size: 18pt; letter-spacing: 0.08em; text-transform: uppercase; }
        .t8-head .sub { margin: 4px 0 0; font-size: 10pt; }
        .t8-contact { text-align: center; font-size: 9pt; margin-top: 6px; }
        .rc-h2 { font-size: 9pt; margin: 14px 0 6px; text-transform: uppercase; letter-spacing: 0.2em; border-bottom: 1px solid #78350f; padding-bottom: 2px; }
        .rc-h2:first-child { margin-top: 0; }
        .rc-block { margin-bottom: 8px; }
        .rc-muted { font-size: 9pt; }
        .rc-ul { margin: 0; padding-left: 18px; }
        .rc-summary { margin: 0 0 8px; line-height: 1.55; }
        .rc-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .rc-table th, .rc-table td { border: 1px dashed #a8a29e; padding: 3px 5px; text-align: left; }
        .t8-skills { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
        .t8-skills span { border: 1px solid #78350f; padding: 2px 8px; font-size: 9pt; }
    </style>
</head>
<body>
@php extract(\App\Support\ResumeHtmlViewComposer::data($resume), EXTR_SKIP); @endphp
<div class="a4-doc t8">
    <header class="t8-head">
        @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
        @if($F::filled($resume['professional_title'] ?? null))<p class="sub">— {{ e($resume['professional_title']) }} —</p>@endif
        @php $meta = array_filter([$resume['email'] ?? '', $resume['mobile'] ?? '', $resume['location'] ?? ''], fn ($s) => $F::filled($s)); @endphp
        @if($meta !== [])<p class="t8-contact">{{ e(implode('  |  ', $meta)) }}</p>@endif
    </header>
    @if($skillsShow !== [])
        <div class="t8-skills">@foreach(array_slice($skillsShow, 0, 16) as $s)<span>{{ e($s) }}</span>@endforeach</div>
    @endif
    @include('resume.html._main_blocks')
    @if($langsShow !== [] || $certsShow !== [])
        @if($langsShow !== [])
            <h2 class="rc-h2">Languages</h2>
            <ul class="rc-ul">@foreach($langsShow as $l)<li>{{ e($l) }}</li>@endforeach</ul>
        @endif
        @if($certsShow !== [])
            <h2 class="rc-h2">Certifications</h2>
            <ul class="rc-ul">@foreach($certsShow as $c)<li>{{ e($c) }}</li>@endforeach</ul>
        @endif
    @endif
</div>
</body>
</html>
