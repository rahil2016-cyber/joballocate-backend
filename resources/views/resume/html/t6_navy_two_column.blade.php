<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background: transparent; color: #1a202c; }
        .t6-doc { padding: 0 !important; overflow: visible !important; }
        .t6-top {
            display: flex;
            align-items: center;
            gap: 5mm;
            background: #152238;
            color: #fff;
            padding: 5mm 6mm;
        }
        .t6-top .rc-photo, .t6-top .rc-photo-ph {
            width: 22mm;
            height: 22mm;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.35);
            flex-shrink: 0;
            margin: 0;
        }
        .t6-top .rc-photo-ph {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e2e8f0;
            color: #152238;
            font-weight: 800;
            font-size: 10pt;
        }
        .t6-top h1 {
            margin: 0;
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .t6-top .role { margin: 3px 0 0; font-size: 10pt; color: #cbd5e1; font-weight: 500; }
        .t6-page { display: flex; align-items: flex-start; min-height: 0; }
        .t6-side {
            width: 32%;
            flex-shrink: 0;
            background: #f1f5f9;
            padding: 5mm 4mm;
            font-size: 8.8pt;
            line-height: 1.45;
        }
        .t6-main {
            flex: 1;
            min-width: 0;
            padding: 5mm 6mm 8mm;
            font-size: 9pt;
            line-height: 1.45;
            border-left: 1px solid #e2e8f0;
        }
        .t6-side .rc-h2, .t6-main .rc-h2 {
            font-size: 8.5pt;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #152238;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #152238;
        }
        .t6-side .rc-h2:first-child, .t6-main .rc-h2:first-child { margin-top: 0; }
        .t6-side .rc-h2 { color: #152238; border-color: #94a3b8; }
        .t6-side .rc-p { margin: 0 0 8px; }
        .t6-side .rc-ul { margin: 0; padding-left: 16px; }
        .t6-side .rc-ul li { margin-bottom: 3px; }
        .t6-main .rc-block { margin-bottom: 10px; }
        .t6-main .rc-block strong { color: #152238; font-size: 9.5pt; }
        .t6-main .rc-muted { color: #64748b; font-size: 8.5pt; }
        .t6-main .rc-summary { margin: 0 0 10px; text-align: justify; }
        .t6-main .rc-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 8px; }
        .t6-main .rc-table th, .t6-main .rc-table td { border: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; }
        .t6-main .rc-table th { background: #f8fafc; width: 38%; }
        .t6-main .rc-ul { margin: 0; padding-left: 16px; }
    </style>
</head>
<body>
@include('resume.html._resume_setup')
@php $showPhotoPlaceholder = true; @endphp
<div class="a4-doc t6-doc">
    <header class="t6-top">
        @if($F::filled($photoUrl))
            <img class="rc-photo" src="{{ e($photoUrl) }}" alt="">
        @else
            <div class="rc-photo-ph">{{ e($initials !== '' ? $initials : '?') }}</div>
        @endif
        <div>
            @if($F::filled($resume['full_name'] ?? null))
                <h1>{{ e($resume['full_name']) }}</h1>
            @endif
            @if($F::filled($resume['professional_title'] ?? null))
                <p class="role">{{ e($resume['professional_title']) }}</p>
            @endif
        </div>
    </header>
    <div class="t6-page">
        <aside class="t6-side">
            @php $asideSkipPhoto = true; @endphp
            @include('resume.html._aside_blocks')
        </aside>
        <main class="t6-main">
            @include('resume.html._main_blocks')
        </main>
    </div>
</div>
</body>
</html>
