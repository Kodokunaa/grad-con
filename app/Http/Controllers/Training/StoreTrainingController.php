<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingRequest;
use App\Mail\TrainingOpportunityMail;
use App\Models\Training;
use App\Models\User;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class StoreTrainingController extends Controller
{
    public function __invoke(StoreTrainingRequest $request)
    {
        $data = $request->safe()->except('image');
        $data['posted_by'] = $request->user()->id;
        if ($file = $request->file('image')) {
            $data['image'] = 'training_'.Str::uuid().'.'.$file->extension();
            abort_unless(PrivateUploads::store($file, 'trainings', $data['image']), 500, 'Image upload failed.');
        }

        try {
            $training = new Training;
            $training->forceFill($data)->save();
        } catch (\Throwable $exception) {
            PrivateUploads::delete('trainings', $data['image'] ?? null);
            throw $exception;
        }

        $recipients = User::query()
            ->where('role', 'alumni')->where('is_active', 1)
            ->where('employment_status', 'Unemployed')->whereNotNull('email')->where('email', '<>', '')
            ->when($training->target_course !== 'Open for All', fn ($query) => $query->where('course', $training->target_course))
            ->get();
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->queue(new TrainingOpportunityMail($training, $recipient));
        }

        return to_route('admin.trainings_create')->with('status', 'Training posted and notifications queued.');
    }
}
