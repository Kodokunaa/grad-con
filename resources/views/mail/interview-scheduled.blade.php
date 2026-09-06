<x-mail::message title="Interview Invitation" eyebrow="Schedule Update">
# Interview invitation

Hello {{ $interview->alumni->fullname }},

Your interview for **{{ $interview->job->title }}** is scheduled for {{ $interview->interview_date->format('F j, Y') }} at {{ $interview->interview_time }}.

**Location or meeting link:** {{ $interview->location }}

{{ $interview->message }}
</x-mail::message>
