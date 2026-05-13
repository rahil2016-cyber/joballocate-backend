<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; }
        .hero { background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 36px 28px 48px; color: #fff; }
        .hero h1 { margin: 0; font-size: 30px; font-weight: 800; }
        .hero p { margin: 8px 0 0; opacity: .95; font-size: 14px; }
        .body { max-width: 880px; margin: -24px auto 0; background: #1e293b; border-radius: 16px 16px 0 0; padding: 28px 26px 40px; display: grid; grid-template-columns: 38% 1fr; gap: 24px; }
        .card { background: #334155; border-radius: 12px; padding: 16px; margin-bottom: 14px; }
        .card h3 { margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .12em; color: #a5b4fc; }
        ul { margin: 0; padding-left: 16px; font-size: 13px; }
        li { margin-bottom: 4px; }
        h2 { font-size: 13px; color: #c7d2fe; text-transform: uppercase; letter-spacing: .1em; margin: 0 0 12px; }
        .exp { margin-bottom: 16px; font-size: 13px; }
        .exp strong { color: #fff; font-size: 14px; }
        .exp em { color: #94a3b8; font-style: normal; font-size: 12px; }
    </style>
</head>
<body>
<div class="hero">
    <h1>{{ e($resume['full_name']) }}</h1>
    <p>{{ e($resume['professional_title']) }} · {{ e($resume['location']) }}</p>
    <p>{{ e($resume['mobile']) }} · {{ e($resume['email']) }}</p>
</div>
<div class="body">
    <aside>
        <div class="card"><h3>Skills</h3><ul>@foreach($resume['skills'] as $s)<li>{{ e($s) }}</li>@endforeach</ul></div>
        <div class="card"><h3>Languages</h3><ul>@foreach($resume['languages'] as $l)<li>{{ e($l) }}</li>@endforeach</ul></div>
        <div class="card"><h3>Certifications</h3><ul>@foreach($resume['certifications'] as $c)<li>{{ e($c) }}</li>@endforeach</ul></div>
    </aside>
    <main>
        <h2>Summary</h2>
        <p style="font-size:14px;line-height:1.6">{{ nl2br(e($resume['summary'])) }}</p>
        <h2>Education</h2>
        @foreach($resume['education_list'] as $ed)
            <div class="exp"><strong>{{ e($ed['title']) }}</strong><br><em>{{ e($ed['institution']) }} · {{ e($ed['year']) }}</em></div>
        @endforeach
        <h2>Work</h2>
        @foreach($resume['work_experience'] as $x)
            <div class="exp"><strong>{{ e($x['heading']) }}</strong> <em>{{ e($x['dates']) }}</em><p>{{ nl2br(e($x['body'])) }}</p></div>
        @endforeach
        <h2>Projects</h2>
        @foreach($resume['projects'] as $x)
            <div class="exp"><strong>{{ e($x['heading']) }}</strong> <em>{{ e($x['dates']) }}</em></div>
        @endforeach
    </main>
</div>
</body>
</html>
