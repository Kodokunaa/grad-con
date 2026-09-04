Hello {{ $recipient->fullname }},

A new training has been posted.

Title: {{ $training->title }}
Date: {{ $training->training_date?->format('F j, Y') }}
Location: {{ $training->location ?: 'To be announced' }}
Target Course: {{ $training->target_course }}

{{ $training->content }}
