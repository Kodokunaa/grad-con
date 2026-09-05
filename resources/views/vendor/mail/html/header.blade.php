@props(['url'])
<tr>
<td class="header">
<table class="header-card" align="center" width="640" cellpadding="0" cellspacing="0" role="presentation" bgcolor="#f97316" style="background-color:#f97316;background-image:linear-gradient(135deg,#f97316 0%,#ea580c 100%);">
<tr>
<td>
<a href="{{ $url }}" style="display:block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
</table>
</td>
</tr>
