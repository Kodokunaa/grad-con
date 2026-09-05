<x-mail::message title="Offer Accepted" eyebrow="Employer Update">
# Job offer accepted

{{ $offer->alumni->fullname }} has accepted **{{ $offer->subject }}**.

<x-mail::button :url="route('employer.job_offers')">View job offers</x-mail::button>
</x-mail::message>
