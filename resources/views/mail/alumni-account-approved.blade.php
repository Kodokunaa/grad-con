<x-mail::message title="Account Approved" eyebrow="Alumni Account Update">
# Account approved

Dear {{ $alumni->fullname }},

Your alumni account has been approved. You can now sign in with the username **{{ $alumni->username }}**.

<x-mail::button :url="route('login')">
Sign in to GradConn
</x-mail::button>

Thanks,  
{{ config('app.name') }}
</x-mail::message>
