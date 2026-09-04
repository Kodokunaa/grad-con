<h2>New Training Opportunity</h2>
<p>Hello <strong>{{ $recipient->fullname }}</strong>,</p>
<p>A new training has been posted for <strong>{{ $training->target_course }}</strong>.</p>
<table>
    <tr><th align="left">Title</th><td>{{ $training->title }}</td></tr>
    <tr><th align="left">Date</th><td>{{ $training->training_date?->format('F j, Y') }}</td></tr>
    <tr><th align="left">Location</th><td>{{ $training->location ?: 'To be announced' }}</td></tr>
</table>
<p><strong>Description:</strong></p>
<p>{!! nl2br(e($training->content)) !!}</p>
<p>Please log in to your alumni account for more details.</p>
