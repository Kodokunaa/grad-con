<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Models\SecurityLog;
use App\Support\PrivateUploads;
use App\Support\ViewFormatter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateAccountProfileController extends Controller
{
    public function __invoke(UpdateAccountProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->safe()->except('profile_picture');
        $data['has_multiple_branches'] = $request->boolean('has_multiple_branches');
        if ($user->role === 'alumni') {
            $data['age'] = $request->filled('birthdate') ? $request->date('birthdate')->age : null;
            $employmentStatus = $data['employment_status'] ?? $user->employment_status;
            $data['job_aligned'] = $employmentStatus === 'Employed' ? ViewFormatter::profile_analyze_course_job_alignment((string) $user->course, (string) $user->employmentHistory()->latest('start_date')->value('job_title'), (string) $user->employmentHistory()->latest('start_date')->value('job_description'))['value'] : null;
            $data['has_multiple_branches'] = 0;
            $data['branch_location'] = null;
        } elseif ($user->role === 'employer') {
            foreach (['birthdate', 'gender', 'civil_status', 'contact_number', 'indigenous_tribe', 'special_needs', 'employment_status', 'career_objective', 'skills'] as $field) {
                $data[$field] = null;
            }$data['branch_location'] = $data['has_multiple_branches'] ? ($data['branch_location'] ?? null) : null;
        } else {
            $data = array_intersect_key($data, array_flip(['fullname', 'email']));
        }
        $old = $user->profile_picture;
        $new = $old;
        if ($file = $request->file('profile_picture')) {
            $new = 'u'.$user->id.'_'.Str::uuid().'.'.$file->extension();
            if (! PrivateUploads::store($file, 'profiles', $new)) {
                throw ValidationException::withMessages(['profile_picture' => 'The profile picture could not be stored. Please try again.']);
            }
            $data['profile_picture'] = $new;
        }
        try {
            DB::transaction(function () use ($user, $data, $request) {
                $user->forceFill($data)->save();
                $log = new SecurityLog;
                $log->forceFill(['user_id' => $user->id, 'action' => 'PROFILE_UPDATED', 'details' => 'Profile info updated', 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])->save();
            });
        } catch (\Throwable $e) {
            if ($new !== $old) {
                PrivateUploads::delete('profiles', $new);
            }throw $e;
        }if ($new !== $old) {
            PrivateUploads::delete('profiles', $old);
        }
        Cache::forget('feed.mention-users.v1');

        return to_route('profile')->with('status', 'Profile updated successfully.');
    }
}
