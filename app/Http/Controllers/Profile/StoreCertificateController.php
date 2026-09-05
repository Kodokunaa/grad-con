<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificateRequest;
use App\Models\AlumniCertificate;
use App\Models\SecurityLog;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class StoreCertificateController extends Controller
{
    public function __invoke(StoreCertificateRequest $request)
    {
        $file = $request->file('certificate_image');
        $name = 'cert_'.$request->user()->id.'_'.Str::uuid().'.'.$file->extension();
        if (! PrivateUploads::store($file, 'certificates', $name)) {
            throw ValidationException::withMessages(['certificate_image' => 'The certificate image could not be stored. Please try again.']);
        }
        try {
            DB::transaction(function () use ($request, $name) {
                $cert = new AlumniCertificate;
                $cert->forceFill(['user_id' => $request->user()->id, 'certificate_name' => $request->input('certificate_name'), 'issuer' => '', 'issue_date' => $request->input('issue_date'), 'certificate_image' => $name])->save();
                $log = new SecurityLog;
                $log->forceFill(['user_id' => $request->user()->id, 'action' => 'CERTIFICATE_ADDED', 'details' => 'Certificate added', 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])->save();
            });
        } catch (\Throwable $e) {
            PrivateUploads::delete('certificates', $name);
            throw $e;
        }

        return to_route('profile')->with('status', 'Certificate added successfully.');
    }
}
