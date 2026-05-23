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
        .t10 { padding: 0 !important; overflow: visible !important; }
        .t10-hero {
            background: linear-gradient(120deg, #f97316 0%, #ec4899 55%, #8b5cf6 100%);
            color: #fff;
            padding: 6mm 7mm;
        }
        .t10-hero h1 { margin: 0; font-size: 19pt; font-weight: 800; }
        .t10-hero .sub { margin: 4px 0 0; font-size: 10.5pt; opacity: .95; }
        .t10-hero .meta { margin-top: 8px; font-size: 9pt; opacity: .9; }
        .t10-body { padding: 5mm 7mm 8mm; display: flex; gap: 5mm; }
        .t10-side { width: 32%; background: #fff7ed; border-radius: 10px; padding: 4mm; font-size: 8.8pt; }
        .t10-main { flex: 1; min-width: 0; font-size: 9pt; }
        .rc-h2 { font-size: 8.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #ea580c; margin: 12px 0 6px; }
        .rc-h2:first-child { margin-top: 0; }
        .rc-block { margin-bottom: 9px; }
        .rc-muted { color: #9a3412; font-size: 8.5pt; opacity: .85; }
        .rc-ul { margin: 0; padding-left: 16px; }
        .rc-p { margin: 0 0 6px; }
        .rc-photo { width: 18mm; height: 18mm; border-radius: 50%; object-fit: cover; border: 2px solid #fdba74; margin-bottom: 8px; }
        .rc-photo-ph { width: 18mm; height: 18mm; border-radius: 50%; background: #ffedd5; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #c2410c; margin-bottom: 8px; }
        .rc-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
        .rc-table th, .rc-table td { border: 1px solid #fed7aa; padding: 4px 6px; }
        .rc-summary { line-height: 1.5; }
    </style>
</head>
<body>
@php extract(\App\Support\ResumeHtmlViewComposer::data($resume), EXTR_SKIP); @endphp
@php $showPhotoPlaceholder = true; @endphp
<div class="a4-doc t10">
    <header class="t10-hero">
        @if($F::filled($resume['full_name'] ?? null))<h1>{{ e($resume['full_name']) }}</h1>@endif
        @if($F::filled($resume['professional_title'] ?? null))<p class="sub">{{ e($resume['professional_title']) }}</p>@endif
        @php $meta = array_filter([$resume['email'] ?? '', $resume['mobile'] ?? ''], fn ($s) => $F::filled($s)); @endphp
        @if($meta !== [])<p class="meta">{{ e(implode(' · ', $meta)) }}</p>@endif
    </header>
    <div class="t10-body">
        <aside class="t10-side">@include('resume.html._aside_blocks')</aside>
        <main class="t10-main">@include('resume.html._main_blocks')</main>
    </div>
</div>
</body>
</html>
