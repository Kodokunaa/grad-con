<x-mail::message title="New Application Letter" eyebrow="Application Update">
# New application letter

The application letter from **{{ $application->alumni->fullname }}** is attached to this email.

<x-mail::panel>
Position: **{{ $application->job->title }}**<br>
Applicant: {{ $application->alumni->fullname }}
</x-mail::panel>

Please review the application when you are ready.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
