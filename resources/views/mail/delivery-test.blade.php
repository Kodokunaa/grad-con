<x-mail::message title="Email Delivery Confirmed" eyebrow="System Check">
# Your GradConn email is working

This message confirms that GradConn can deliver branded notifications through the configured email provider.

<x-mail::panel>
Provider connection: **Successful**  
Application: **{{ config('app.name') }}**  
Sent at: **{{ now()->format('F j, Y \a\t g:i A') }}**
</x-mail::panel>

You can now receive account approvals, password resets, job opportunities, application updates, offers, and interview schedules.

Thanks,  
{{ config('app.name') }}
</x-mail::message>
