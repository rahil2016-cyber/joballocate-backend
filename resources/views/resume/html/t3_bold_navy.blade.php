<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    <style>
        body { margin: 0; font-family: 'Georgia', serif; background: #eef1f6; color: #0f172a; }
        .bar { height: 8px; background: linear-gradient(90deg, #1e3a5f, #3b82f6); }
        .wrap { max-width: 780px; margin: 0 auto; background: #fff; padding: 32px 40px 48px; }
        h1 { font-family: system-ui, sans-serif; font-size: 32px; color: #1e3a5f; margin: 0; font-weight: 900; }
        .headline { font-family: system-ui, sans-serif; color: #3b82f6; font-weight: 600; margin: 6px 0 20px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; font-family: system-ui, sans-serif; font-size: 13px; }
        h2 { font-family: system-ui, sans-serif; font-size: 13px; color: #1e3a5f; text-transform: uppercase; letter-spacing: .08em; margin: 26px 0 10px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        .skill { display: inline-block; background: #e8eef9; padding: 4px 10px; border-radius: 20px; margin: 3px; font-size: 12px; font-family: system-ui, sans-serif; }
    </style>
</head>
<body>
<div class="bar"></div>
<div class="wrap">
    <h1>{{ e($resume['full_name']) }}</h1>
    @if($resume['professional_title'])<div class="headline">{{ e($resume['professional_title']) }}</div>@endif
    <div class="grid">
        <div><strong>Phone</strong><br>{{ e($resume['mobile']) }}</div>
        <div><strong>Email</strong><br>{{ e($resume['email']) }}</div>
        <div><strong>Location</strong><br>{{ e($resume['location']) }}</div>
        <div><strong>DOB / Gender</strong><br>{{ e($resume['dob']) }} · {{ e($resume['gender']) }}</div>
    </div>
    <h2>Profile</h2>
    <p style="font-size:15px;line-height:1.6">{{ nl2br(e($resume['summary'])) }}</p>
    <h2>Core skills</h2>
    <div>@foreach($resume['skills'] as $s)<span class="skill">{{ e($s) }}</span>@endforeach</div>
    <h2>Education</h2>
    @foreach($resume['education_list'] as $ed)
        <p><strong>{{ e($ed['title']) }}</strong> — {{ e($ed['institution']) }} <em>({{ e($ed['year']) }})</em></p>
    @endforeach
    <h2>Work</h2>
    @foreach($resume['work_experience'] as $x)
        <p><strong>{{ e($x['heading']) }}</strong> <em>{{ e($x['dates']) }}</em><br>{{ nl2br(e($x['body'])) }}</p>
    @endforeach
    <h2>Projects & internships</h2>
    @foreach(array_merge($resume['internships'], $resume['projects']) as $x)
        <p><strong>{{ e($x['heading']) }}</strong> <em>{{ e($x['dates']) }}</em></p>
    @endforeach
</div>
</body>
</html>
