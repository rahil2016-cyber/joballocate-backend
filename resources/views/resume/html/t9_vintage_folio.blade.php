<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, 'Times New Roman', serif; background: transparent; color: #292524; }
        .t9 { padding: 6mm 8mm 9mm !important; background: #faf7f2 !important; }
        .t9-frame { border: 1px solid #a8a29e; padding: 5mm 6mm; }
        .t9-head { text-align: center; padding-bottom: 4mm; margin-bottom: 4mm; border-bottom: 2px solid #78716c; }
        .t9-head h1 { margin: 0; font-size: 22pt; font-weight: 400; font-style: italic; color: #44403c; }
        .t9-head .sub { margin: 6px 0 0; font-size: 11pt; letter-spacing: 0.06em; }
        .t9-grid { display: flex; gap: 6mm; }
        .t9-side { width: 34%; font-size: 9.5pt; line-height: 1.5; }
        .t9-main { flex: 1; min-width: 0; font-size: 10pt; line-height: 1.5; }
        .rc-h2 { font-size: 10pt; font-variant: small-caps; letter-spacing: 0.12em; color: #57534e; margin: 12px 0 8px; }
        .rc-photo { width: 24mm; height: 28mm; object-fit: cover; border: 1px solid #a8a29e; display: block; margin: 0 auto 10px; }
        .rc-photo-ph { width: 24mm; height: 28mm; border: 1px solid #a8a29e; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; background: #e7e5e4; font-style: italic; }
        .rc-block { margin-bottom: 10px; }
        .rc-muted { color: #78716c; font-size: 9.5pt; }
        .rc-ul { margin: 0; padding-left: 18px; }
        .rc-p { margin: 0 0 8px; }
        .rc-table { width: 100%; font-size: 9.5pt; border-collapse: collapse; }
        .rc-table th, .rc-table td { border-bottom: 1px solid #d6d3d1; padding: 4px 0; }
        .rc-table th { text-align: left; width: 40%; color: #57534e; font-weight: 600; }
    </style>
</head>
<body>
@include('resume.html._resume_setup')
@php $showPhotoPlaceholder = true; @endphp
<div class="a4-doc t9">
    <div class="t9-frame">
        <header class="t9-head">
            @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
            @if($F::filled($resume['professional_title'] ?? null))<p class="sub">{{ e($resume['professional_title']) }}</p>@endif
        </header>
        <div class="t9-grid">
            <aside class="t9-side">@include('resume.html._aside_blocks')</aside>
            <main class="t9-main">@include('resume.html._main_blocks')</main>
        </div>
    </div>
</div>
</body>
</html>
