<x-mail::message>
# New training opportunity

Hello {{ $recipient->fullname }},

A new training opportunity is available for **{{ $training->target_course }}**.

<x-mail::panel>
**{{ $training->title }}**<br>
Date: {{ $training->training_date?->format('F j, Y') }}<br>
Location: {{ $training->location ?: 'To be announced' }}
</x-mail::panel>

{{ $training->content }}

<x-mail::button :url="route('alumni.feed')">
View training details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
