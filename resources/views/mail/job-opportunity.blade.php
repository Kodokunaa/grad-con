<x-mail::message>
# {{ $customSubject ?: 'New Job Opportunity' }}

Hello {{ $recipient->fullname ?: 'Alumni' }},

{{ $customMessage ?: 'A new job opportunity has been posted. Review the details below.' }}

**Job:** {{ $job->title }}  
**Company:** {{ $job->company ?: $job->employer_company }}  
**Location:** {{ $job->location ?: 'Not specified' }}  
**Type:** {{ $job->job_type ?: 'Not specified' }}

{{ $job->description }}

<x-mail::button :url="url('/alumni/job_details.php?id='.$job->id)">
View Job
</x-mail::button>

Thanks,  
{{ config('app.name') }}
</x-mail::message>
