@props([
    'title' => 'GradConn Notification',
    'eyebrow' => 'Official GradConn Update',
])
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<span class="brand-line"><img class="email-logo" src="{{ asset('ccc3d.png') }}" width="64" alt="City College of Calapan logo"><span class="brand-text">{{ config('app.name') }}</span></span>
<span class="brand-eyebrow">{{ $eyebrow }}</span>
<span class="brand-title">{{ $title }}</span>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
