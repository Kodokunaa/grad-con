<x-mail::message title="New Applicant Résumé" eyebrow="Application Update">
# New applicant resume

The resume for **{{ $application->alumni->fullname }}** is attached to this email.

<x-mail::panel>
Position: **{{ $application->job->title }}**<br>
Applicant: {{ $application->alumni->fullname }}
</x-mail::panel>

Please review the attachment when you are ready.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
