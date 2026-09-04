<x-mail::message>
# Interview invitation

Hello {{ $interview->alumni->fullname }},

Your interview for **{{ $interview->job?->title ?: $interview->offer?->subject }}** is scheduled for {{ $interview->interview_date->format('F j, Y') }} at {{ $interview->interview_time }}.

**Location or meeting link:** {{ $interview->location }}

{{ $interview->message }}
</x-mail::message>
