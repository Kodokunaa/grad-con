<x-mail::message title="Job Offer" eyebrow="Career Opportunity">
# {{ $offer->subject }}

Hello {{ $offer->alumni->fullname ?: 'Alumni' }},

{{ $offer->message }}

This job offer was sent by **{{ $offer->employer->fullname }}** through {{ config('app.name') }}.

Regards,  
{{ config('app.name') }}
</x-mail::message>
