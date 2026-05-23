<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, serif; background: transparent; }
        .t12 { padding: 0 !important; background: #0c1929 !important; color: #e2e8f0 !important; }
        .t12-inner { border: 2px solid #c9a227; margin: 4mm; padding: 5mm 6mm 7mm; }
        .t12-head { text-align: center; padding-bottom: 4mm; margin-bottom: 4mm; border-bottom: 1px solid #c9a227; }
        .t12-head h1 { margin: 0; font-size: 20pt; font-weight: 600; color: #f8fafc; letter-spacing: 0.04em; }
        .t12-head .sub { margin: 6px 0 0; color: #c9a227; font-size: 11pt; }
        .t12-grid { display: flex; gap: 5mm; align-items: flex-start; }
        .t12-side { width: 31%; font-size: 8.8pt; line-height: 1.45; }
        .t12-main { flex: 1; min-width: 0; font-size: 9pt; line-height: 1.45; }
        .rc-h2 { font-size: 8.5pt; color: #c9a227; text-transform: uppercase; letter-spacing: 0.14em; margin: 12px 0 6px; font-weight: 700; }
        .rc-h2:first-child { margin-top: 0; }
        .rc-block strong { color: #f8fafc; }
        .rc-muted { color: #94a3b8; font-size: 8.5pt; }
        .rc-ul { margin: 0; padding-left: 16px; }
        .rc-p { margin: 0 0 6px; }
        .rc-p strong { color: #c9a227; }
        .rc-photo { width: 22mm; height: 22mm; border-radius: 50%; object-fit: cover; border: 2px solid #c9a227; display: block; margin: 0 auto 10px; }
        .rc-photo-ph { width: 22mm; height: 22mm; border-radius: 50%; border: 2px solid #c9a227; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; background: #1e293b; color: #c9a227; font-weight: 700; }
        .rc-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .rc-table th, .rc-table td { border: 1px solid #334155; padding: 4px 6px; }
        .rc-table th { color: #c9a227; background: #132337; }
        .rc-summary { color: #cbd5e1; }
    </style>
</head>
<body class="a4-body--dark">
@include('resume.html._resume_setup')
@php $showPhotoPlaceholder = true; @endphp
<div class="a4-doc a4-doc--dark t12">
    <div class="t12-inner">
        <header class="t12-head">
            @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
            @if($F::filled($resume['professional_title'] ?? null))<p class="sub">{{ e($resume['professional_title']) }}</p>@endif
        </header>
        <div class="t12-grid">
            <aside class="t12-side">@include('resume.html._aside_blocks')</aside>
            <main class="t12-main">@include('resume.html._main_blocks')</main>
        </div>
    </div>
</div>
</body>
</html>
