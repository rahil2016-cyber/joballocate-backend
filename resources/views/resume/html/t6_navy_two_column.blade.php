<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    @include('resume.html._a4_styles')
    <style>
        :root {
            --navy: #152238;
            --navy-mid: #1a2d4a;
            --paper: #ffffff;
            --muted: #5c6570;
            --rule: #d8dee9;
            --deco-fill: #e8ecf2;
        }
        body { margin: 0; font-family: 'Segoe UI', system-ui, -apple-system, Roboto, Helvetica, Arial, sans-serif; background: transparent; color: #1a1a1a; }
        .t6 { position: relative; overflow: hidden; padding: 0 !important; background: var(--paper) !important; }
        .t6 .deco {
            position: absolute;
            border-radius: 50%;
            background-color: var(--deco-fill);
            background-image: radial-gradient(circle, #9aa5b8 1.1px, transparent 1.1px);
            background-size: 5px 5px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.95;
        }
        .t6 .deco-tr { width: 72px; height: 72px; top: 10mm; right: 8mm; }
        .t6 .deco-ml { width: 48px; height: 48px; top: 52%; left: -6px; transform: translateY(-50%); }
        .t6 .deco-br { width: 140px; height: 140px; bottom: -20px; right: -10px; }
        .t6-hero {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            min-height: 38mm;
            padding: 7mm 8mm 5mm 0;
        }
        .t6-navy-block {
            position: absolute;
            left: 0;
            top: 0;
            width: 42%;
            height: 44mm;
            background: var(--navy);
            z-index: 0;
        }
        .t6-photo-wrap {
            position: relative;
            z-index: 2;
            flex-shrink: 0;
            margin-left: 7mm;
            margin-bottom: 0;
        }
        .t6-photo {
            width: 28mm;
            height: 28mm;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--navy-mid);
            display: block;
            background: #e2e8f0;
        }
        .t6-photo-ph {
            width: 28mm;
            height: 28mm;
            border-radius: 50%;
            border: 3px solid var(--navy-mid);
            background: linear-gradient(145deg, #e2e8f0, #cbd5e1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11pt;
            font-weight: 800;
            color: var(--navy);
        }
        .t6-headline-block {
            flex: 1;
            padding-left: 6mm;
            padding-bottom: 2mm;
            position: relative;
            z-index: 1;
        }
        .t6-name {
            margin: 0;
            font-size: 22pt;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--navy);
            line-height: 1.1;
        }
        .t6-role {
            margin: 3px 0 0;
            font-size: 11.5pt;
            font-weight: 500;
            color: var(--muted);
        }
        .t6-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 32% 1fr;
            gap: 0 5mm;
            padding: 0 7mm 8mm;
        }
        .t6-aside { font-size: 8.8pt; line-height: 1.45; color: #2d3748; }
        .t6-main { font-size: 9pt; line-height: 1.45; color: #1a202c; border-left: 1px solid var(--rule); padding-left: 5mm; }
        .t6-h-aside { margin: 0 0 6px; font-size: 9.5pt; font-weight: 800; color: var(--navy); }
        .t6-about { margin: 0 0 10px; text-align: justify; hyphens: auto; }
        .t6-bar {
            margin: 12px 0 8px;
            padding: 5px 8px;
            background: var(--navy);
            color: #fff;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-align: center;
            text-transform: uppercase;
        }
        .t6-bar:first-child { margin-top: 0; }
        .t6-ul { margin: 0; padding-left: 16px; }
        .t6-ul li { margin-bottom: 3px; }
        .t6-contact { margin: 10px 0 0; }
        .t6-contact-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
        .t6-icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .t6-icon svg { width: 11px; height: 11px; fill: #fff; }
        .t6-contact-txt { font-size: 8.5pt; color: #2d3748; padding-top: 2px; word-break: break-word; }
        .t6-main .t6-bar { margin-top: 0; }
        .t6-main .t6-bar + * { margin-top: 8px; }
        .t6-exp { margin-bottom: 11px; }
        .t6-exp strong { display: block; font-size: 9.5pt; color: var(--navy); }
        .t6-exp .loc { font-size: 8.2pt; color: var(--muted); margin-top: 1px; }
        .t6-exp .dates { font-size: 8.8pt; font-weight: 800; color: var(--navy); margin-top: 2px; }
        .t6-exp .body { margin: 4px 0 0; font-size: 8.6pt; color: #374151; }
        .t6-edu { margin-bottom: 9px; }
        .t6-edu strong { font-size: 9.2pt; color: var(--navy); }
        .t6-edu .deg { font-size: 8.4pt; color: #374151; margin-top: 2px; }
        .t6-edu .yr { font-size: 8.2pt; color: var(--muted); margin-top: 1px; }
        .t6-skill-row { margin-bottom: 8px; }
        .t6-skill-top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px; }
        .t6-skill-name { font-size: 8.2pt; font-weight: 600; color: var(--navy); }
        .t6-skill-pct { font-size: 8pt; font-weight: 700; color: var(--navy); }
        .t6-track { height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
        .t6-fill { height: 100%; background: var(--navy); border-radius: 3px; }
    </style>
</head>
<body>
@php
    $F = \App\Support\ResumeHtmlFormat::class;
    $summaryPlain = $F::plainMultiline($resume['summary'] ?? null);
    $skillsShow = $F::nonEmptyStrings($resume['skills'] ?? []);
    $langsShow = $F::nonEmptyStrings($resume['languages'] ?? []);
    $hasEdu = $F::hasEducationDisplay($resume['education_list'] ?? [], $resume['graduation'] ?? []);
    $hasWork = $F::hasExperienceBlocks($resume['work_experience'] ?? []);
    $mergedXp = array_merge($resume['work_experience'] ?? [], $resume['internships'] ?? [], $resume['projects'] ?? []);
    $hasXp = $F::hasExperienceBlocks($mergedXp);
@endphp
<div class="a4-doc t6">
    <div class="deco deco-tr" aria-hidden="true"></div>
    <div class="deco deco-ml" aria-hidden="true"></div>
    <div class="deco deco-br" aria-hidden="true"></div>

    <header class="t6-hero">
        <div class="t6-navy-block" aria-hidden="true"></div>
        <div class="t6-photo-wrap">
            @if($F::filled($resume['photo_url'] ?? null))
                <img class="t6-photo" src="{{ e($resume['photo_url']) }}" alt="">
            @else
                @php
                    $ini = '';
                    $nm = trim((string) ($resume['full_name'] ?? ''));
                    if ($nm !== '') {
                        $parts = preg_split('/\s+/', $nm);
                        $a = $parts[0] ?? '';
                        $b = $parts[count($parts) - 1] ?? '';
                        $ini = strtoupper(substr($a, 0, 1).substr($b, 0, 1));
                    }
                @endphp
                <div class="t6-photo-ph">{{ e($ini !== '' ? $ini : '?') }}</div>
            @endif
        </div>
        <div class="t6-headline-block">
            @if($F::filled($resume['full_name'] ?? null))
                <h1 class="t6-name">{{ e(\Illuminate\Support\Str::upper($resume['full_name'])) }}</h1>
            @endif
            @if($F::filled($resume['professional_title'] ?? null))
                <p class="t6-role">{{ e($resume['professional_title']) }}</p>
            @endif
        </div>
    </header>

    <div class="t6-grid">
        <aside class="t6-aside">
            @if($summaryPlain !== '')
                <h2 class="t6-h-aside">About Me</h2>
                <p class="t6-about">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $summaryPlain))) !!}</p>
            @endif

            @if($F::filled($resume['mobile'] ?? null) || $F::filled($resume['email'] ?? null) || $F::filled($resume['location'] ?? null))
                <div class="t6-contact">
                    @if($F::filled($resume['mobile'] ?? null))
                        <div class="t6-contact-row">
                            <span class="t6-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.4 5 6.2 6.2l2-2c.3-.3.8-.4 1.2-.2 1.2.4 2.5.6 3.8.6.7 0 1.2.5 1.2 1.2V20c0 .7-.5 1.2-1.2 1.2C9.4 21.2 2.8 14.6 2.8 6.2 2.8 5.5 3.3 5 4 5h2.5c.7 0 1.2.5 1.2 1.2 0 1.3.2 2.6.6 3.8.1.4 0 .9-.2 1.2l-2 2z"/></svg>
                            </span>
                            <div class="t6-contact-txt">{{ e($resume['mobile']) }}</div>
                        </div>
                    @endif
                    @if($F::filled($resume['email'] ?? null))
                        <div class="t6-contact-row">
                            <span class="t6-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                            </span>
                            <div class="t6-contact-txt">{{ e($resume['email']) }}</div>
                        </div>
                    @endif
                    @if($F::filled($resume['location'] ?? null))
                        <div class="t6-contact-row">
                            <span class="t6-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5c-1.4 0-2.5-1.1-2.5-2.5S10.6 6.5 12 6.5s2.5 1.1 2.5 2.5S13.4 11.5 12 11.5z"/></svg>
                            </span>
                            <div class="t6-contact-txt">{{ e($resume['location']) }}</div>
                        </div>
                    @endif
                </div>
            @endif

            @if($langsShow !== [])
                <div class="t6-bar">Language</div>
                <ul class="t6-ul">
                    @foreach(array_slice($langsShow, 0, 12) as $ln)
                        <li>{{ e($ln) }}</li>
                    @endforeach
                </ul>
            @endif

            @if($skillsShow !== [])
                <div class="t6-bar">Expertise</div>
                <ul class="t6-ul">
                    @foreach(array_slice($skillsShow, 0, 12) as $sk)
                        <li>{{ e($sk) }}</li>
                    @endforeach
                </ul>
            @endif
        </aside>

        <main class="t6-main">
            @if($hasXp)
                <div class="t6-bar">Experience</div>
                @foreach($mergedXp as $x)
                    @if(is_array($x) && ($F::filled($x['heading'] ?? null) || $F::filled($x['dates'] ?? null) || $F::filled($x['body'] ?? null)))
                        <div class="t6-exp">
                            <strong>{{ e($x['heading'] ?? '') }}</strong>
                            @if($F::filled($x['dates'] ?? null))
                                <div class="dates">{{ e($x['dates']) }}</div>
                            @endif
                            @if($F::filled($x['body'] ?? null))
                                <p class="body">{!! nl2br(e(str_replace(["\r\n", "\r"], "\n", $F::plainMultiline($x['body'])))) !!}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            @endif

            @if($hasEdu)
                <div class="t6-bar">Education</div>
                @php $listedEdu = false; @endphp
                @foreach($resume['education_list'] ?? [] as $ed)
                    @if(is_array($ed) && ($F::filled($ed['title'] ?? null) || $F::filled($ed['institution'] ?? null) || $F::filled($ed['year'] ?? null) || $F::filled($ed['marks'] ?? null) || $F::filled($ed['mode'] ?? null)))
                        @php $listedEdu = true; @endphp
                        <div class="t6-edu">
                            <strong>{{ e($ed['institution'] ?? '') }}</strong>
                            <div class="deg">{{ e($ed['title'] ?? '') }}</div>
                            <div class="yr">{{ e(trim(($ed['year'] ?? '').' '.($ed['marks'] ?? ''))) }}</div>
                        </div>
                    @endif
                @endforeach
                @if(! $listedEdu && ($F::filled($resume['graduation']['course'] ?? null) || $F::filled($resume['graduation']['college'] ?? null)))
                    <div class="t6-edu">
                        <strong>{{ e($resume['graduation']['college'] ?? '') }}</strong>
                        <div class="deg">{{ e($resume['graduation']['course'] ?? '') }}</div>
                    </div>
                @endif
            @endif

            @if($skillsShow !== [])
                <div class="t6-bar">Skills Summary</div>
                @foreach(array_values(array_slice($skillsShow, 0, 10)) as $idx => $sk)
                    @php $pct = 52 + (crc32($sk.'|'.$idx) % 44); @endphp
                    <div class="t6-skill-row">
                        <div class="t6-skill-top">
                            <span class="t6-skill-name">{{ e($sk) }}</span>
                            <span class="t6-skill-pct">{{ $pct }}%</span>
                        </div>
                        <div class="t6-track">
                            <div class="t6-fill" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            @endif
        </main>
    </div>
</div>
</body>
</html>
