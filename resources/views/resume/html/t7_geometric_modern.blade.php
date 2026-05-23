<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background: transparent; }
        .t7 { padding: 6mm 7mm 8mm !important; }
        .t7-head { border-left: 6px solid #06b6d4; padding-left: 10px; margin-bottom: 6mm; }
        .t7-head h1 { margin: 0; font-size: 20pt; font-weight: 800; color: #0f172a; }
        .t7-head .sub { margin: 4px 0 0; color: #64748b; font-size: 10.5pt; }
        .t7-meta { font-size: 9pt; color: #475569; margin-top: 6px; }
        .t7-grid { display: flex; gap: 5mm; align-items: flex-start; }
        .t7-side { width: 30%; flex-shrink: 0; background: #f0fdfa; border-radius: 8px; padding: 4mm; font-size: 8.8pt; }
        .t7-main { flex: 1; min-width: 0; font-size: 9pt; }
        .rc-h2 { font-size: 8.5pt; letter-spacing: .14em; text-transform: uppercase; color: #0891b2; margin: 12px 0 6px; font-weight: 800; }
        .rc-h2:first-child { margin-top: 0; }
        .rc-block { margin-bottom: 9px; padding-left: 8px; border-left: 3px solid #e2e8f0; }
        .rc-block strong { color: #0f172a; }
        .rc-muted { color: #64748b; font-size: 8.5pt; }
        .rc-ul { margin: 0; padding-left: 16px; }
        .rc-p { margin: 0 0 6px; }
        .rc-photo { width: 20mm; height: 20mm; border-radius: 8px; object-fit: cover; display: block; margin-bottom: 8px; }
        .rc-photo-ph { width: 20mm; height: 20mm; border-radius: 8px; background: #cffafe; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #0891b2; margin-bottom: 8px; }
        .rc-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .rc-table th, .rc-table td { border: 1px solid #e2e8f0; padding: 4px 6px; }
        .rc-summary { line-height: 1.5; margin: 0 0 8px; }
    </style>
</head>
<body>
@php extract(\App\Support\ResumeHtmlViewComposer::data($resume), EXTR_SKIP); @endphp
@php $showPhotoPlaceholder = true; @endphp
<div class="a4-doc t7">
    <header class="t7-head">
        @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
        @if($F::filled($resume['professional_title'] ?? null))<p class="sub">{{ e($resume['professional_title']) }}</p>@endif
        @php $meta = array_filter([$resume['email'] ?? '', $resume['mobile'] ?? '', $resume['location'] ?? ''], fn ($s) => $F::filled($s)); @endphp
        @if($meta !== [])<p class="t7-meta">{{ e(implode(' · ', $meta)) }}</p>@endif
    </header>
    <div class="t7-grid">
        <aside class="t7-side">@include('resume.html._aside_blocks')</aside>
        <main class="t7-main">@include('resume.html._main_blocks')</main>
    </div>
</div>
</body>
</html>
