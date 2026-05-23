@if(empty($asideSkipPhoto))
    @if($F::filled($photoUrl ?? null))
        <img class="rc-photo" src="{{ e($photoUrl) }}" alt="">
    @elseif(!empty($showPhotoPlaceholder))
        <div class="rc-photo-ph">{{ e($initials !== '' ? $initials : '?') }}</div>
    @endif
@endif
@if($contactAny)
    <h2 class="rc-h2">Get in touch</h2>
    @if($F::filled($resume['mobile'] ?? null))
        <p class="rc-p"><strong>Mobile</strong><br>{{ e($resume['mobile']) }}</p>
    @endif
    @if($F::filled($resume['email'] ?? null))
        <p class="rc-p"><strong>Email</strong><br>{{ e($resume['email']) }}</p>
    @endif
    @if($F::filled($resume['location'] ?? null))
        <p class="rc-p"><strong>Location</strong><br>{{ e($resume['location']) }}</p>
    @endif
@endif
@if($skillsShow !== [])
    <h2 class="rc-h2">Skills</h2>
    <ul class="rc-ul">@foreach($skillsShow as $s)<li>{{ e($s) }}</li>@endforeach</ul>
@endif
@if($langsShow !== [])
    <h2 class="rc-h2">Languages</h2>
    <ul class="rc-ul">@foreach($langsShow as $l)<li>{{ e($l) }}</li>@endforeach</ul>
@endif
@if($certsShow !== [])
    <h2 class="rc-h2">Certifications</h2>
    <ul class="rc-ul">@foreach($certsShow as $c)<li>{{ e($c) }}</li>@endforeach</ul>
@endif
