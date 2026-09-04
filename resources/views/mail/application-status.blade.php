<x-mail::message>
# {{ $action === 'accept' ? 'Application accepted' : 'Interview selection' }}

Hello {{ $application->alumni->fullname }},

Your application for **{{ $application->job->title }}** at **{{ $application->job->company }}** has been {{ $action === 'accept' ? 'accepted' : 'selected for an interview' }}.

{{ $customMessage }}

<x-mail::button :url="route('alumni.my_applications')">View applications</x-mail::button>
</x-mail::message>
