<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; font-size: 14px; color: #1a1a1a; background: #fafafa; }
        .wrap { max-width: 720px; margin: 0 auto; padding: 36px 28px; background: #fff; }
        h1 { font-size: 28px; font-weight: 700; margin: 0 0 4px; letter-spacing: -0.5px; }
        .sub { color: #666; margin-bottom: 24px; }
        h2 { font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #888; margin: 28px 0 10px; border-top: 1px solid #eee; padding-top: 20px; }
        h2:first-of-type { border-top: 0; padding-top: 0; }
        p { line-height: 1.55; margin: 0 0 8px; }
        ul { margin: 0; padding-left: 18px; }
        .row { margin-bottom: 14px; }
        .row b { display: block; }
        .meta { color: #777; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>{{ e($resume['full_name']) }}</h1>
    @if($resume['professional_title'])<div class="sub">{{ e($resume['professional_title']) }}</div>@endif
    <p class="meta">{{ e($resume['mobile']) }} · {{ e($resume['email']) }} · {{ e($resume['location']) }}</p>
    <h2>Summary</h2>
    <p>{{ nl2br(e($resume['summary'] ?: '')) }}</p>
    <h2>Skills</h2>
    <p>{{ e(implode(' · ', $resume['skills'])) }}</p>
    <h2>Education</h2>
    @foreach($resume['education_list'] as $ed)
        <div class="row"><b>{{ e($ed['title']) }}</b>{{ e($ed['institution']) }} — {{ e($ed['year']) }} {{ e($ed['marks']) }}</div>
    @endforeach
    @if(empty($resume['education_list']))
        <p>{{ e($resume['graduation']['course']) }}, {{ e($resume['graduation']['college']) }}</p>
    @endif
    <h2>Experience</h2>
    @foreach(array_merge($resume['work_experience'], $resume['internships']) as $x)
        <div class="row"><b>{{ e($x['heading']) }}</b><span class="meta">{{ e($x['dates']) }}</span>@if($x['body'])<p>{{ nl2br(e($x['body'])) }}</p>@endif</div>
    @endforeach
    <h2>Projects</h2>
    @foreach($resume['projects'] as $x)
        <div class="row"><b>{{ e($x['heading']) }}</b><span class="meta">{{ e($x['dates']) }}</span></div>
    @endforeach
</div>
</body>
</html>
