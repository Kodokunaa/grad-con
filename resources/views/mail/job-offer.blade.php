<x-mail::message title="Job Offer" eyebrow="Career Opportunity">
# {{ $offer->subject }}

Hello {{ $offer->alumni->fullname ?: 'Alumni' }},

{{ $offer->message }}

<x-mail::button :url="route('offers.response.confirm', ['token' => $offer->offer_token, 'action' => 'accept'])">
Review and accept
</x-mail::button>

[Review and decline]({{ route('offers.response.confirm', ['token' => $offer->offer_token, 'action' => 'decline']) }})

Sent by {{ $offer->employer->fullname }} through {{ config('app.name') }}.
</x-mail::message>
