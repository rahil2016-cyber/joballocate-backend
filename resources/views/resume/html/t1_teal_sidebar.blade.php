<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resume['full_name'] ?: 'Resume' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #222; background: #f4f4f4; }
        .page { max-width: 820px; margin: 0 auto; background: #fff; min-height: 100vh; display: flex; }
        .side { width: 32%; background: #0d7377; color: #fff; padding: 28px 20px; }
        .main { flex: 1; padding: 28px 26px; }
        h1 { margin: 0 0 6px; font-size: 26px; color: #0d7377; font-weight: 800; }
        .tag { color: #555; font-size: 13px; margin-bottom: 18px; }
        h2 { font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase; color: #0d7377; border-bottom: 2px solid #0d7377; padding-bottom: 4px; margin: 18px 0 10px; }
        .side h2 { color: #b8fff5; border-color: rgba(255,255,255,.35); }
        ul { margin: 0; padding-left: 18px; }
        li { margin-bottom: 4px; }
        .muted { color: #666; font-size: 12px; }
        .block { margin-bottom: 12px; }
        .block strong { display: block; color: #0d7377; }
        .photo { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #b8fff5; display: block; margin: 0 auto 16px; }
        table.edu { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.edu th, table.edu td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        table.edu th { background: #e8f7f7; color: #0d7377; }
    </style>
</head>
<body>
<div class="page">
    <aside class="side">
        @if(!empty($resume['photo_url']))
            <img class="photo" src="{{ e($resume['photo_url']) }}" alt="">
        @endif
        <h2>Get in touch</h2>
        <p><strong>Mobile</strong><br>{{ e($resume['mobile'] ?: '—') }}</p>
        <p><strong>Email</strong><br>{{ e($resume['email'] ?: '—') }}</p>
        <h2>Skills</h2>
        <ul>
            @foreach($resume['skills'] as $s)
                <li>{{ e($s) }}</li>
            @endforeach
            @if(empty($resume['skills']))<li class="muted">—</li>@endif
        </ul>
        <h2>Languages</h2>
        <ul>
            @foreach($resume['languages'] as $l)
                <li>{{ e($l) }}</li>
            @endforeach
            @if(empty($resume['languages']))<li class="muted">—</li>@endif
        </ul>
        <h2>Certifications</h2>
        <ul>
            @foreach($resume['certifications'] as $c)
                <li>{{ e($c) }}</li>
            @endforeach
            @if(empty($resume['certifications']))<li class="muted">—</li>@endif
        </ul>
    </aside>
    <section class="main">
        <h1>{{ e($resume['full_name']) }}</h1>
        @if(!empty($resume['professional_title']))
            <div class="tag">{{ e($resume['professional_title']) }}</div>
        @endif
        <h2>Resume summary</h2>
        <p>{{ nl2br(e($resume['summary'] ?: '—')) }}</p>
        <h2>Personal details</h2>
        <table class="edu">
            <tr><th>Current location</th><td>{{ e($resume['location'] ?: '—') }}</td></tr>
            <tr><th>Home town</th><td>{{ e($resume['hometown'] ?: '—') }}</td></tr>
            <tr><th>Date of birth</th><td>{{ e($resume['dob'] ?: '—') }}</td></tr>
            <tr><th>Gender</th><td>{{ e($resume['gender'] ?: '—') }}</td></tr>
            <tr><th>Residing in India</th><td>{{ $resume['residing_in_india'] ? 'Yes' : 'No' }}</td></tr>
        </table>
        <h2>Education</h2>
        @foreach($resume['education_list'] as $ed)
            <div class="block"><strong>{{ e($ed['title']) }}</strong>
                <span class="muted">{{ e($ed['institution']) }}</span><br>
                {{ e($ed['year']) }} @if(!empty($ed['marks'])) · {{ e($ed['marks']) }} @endif
                @if(!empty($ed['mode'])) · {{ e($ed['mode']) }} @endif
            </div>
        @endforeach
        @if(empty($resume['education_list']))
            <p class="muted">Graduation: {{ e($resume['graduation']['course']) }} @ {{ e($resume['graduation']['college']) }}</p>
        @endif
        <h2>Internships</h2>
        @foreach($resume['internships'] as $x)
            <div class="block"><strong>{{ e($x['heading']) }}</strong> <span class="muted">{{ e($x['dates']) }}</span>
                @if($x['body'])<p>{{ nl2br(e($x['body'])) }}</p>@endif
            </div>
        @endforeach
        <h2>Projects</h2>
        @foreach($resume['projects'] as $x)
            <div class="block"><strong>{{ e($x['heading']) }}</strong> <span class="muted">{{ e($x['dates']) }}</span>
                @if($x['body'])<p>{{ nl2br(e($x['body'])) }}</p>@endif
            </div>
        @endforeach
        <h2>Work experience</h2>
        @foreach($resume['work_experience'] as $x)
            <div class="block"><strong>{{ e($x['heading']) }}</strong> <span class="muted">{{ e($x['dates']) }}</span>
                @if($x['body'])<p>{{ nl2br(e($x['body'])) }}</p>@endif
            </div>
        @endforeach
        @if(!empty($resume['academic_achievements']))
            <h2>Academic achievements</h2>
            <ul>@foreach($resume['academic_achievements'] as $a)<li>{{ e($a) }}</li>@endforeach</ul>
        @endif
    </section>
</div>
</body>
</html>
