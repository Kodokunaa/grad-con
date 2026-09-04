<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\AlumniCertificate;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Gate;

final class DestroyCertificateController extends Controller
{
    public function __invoke(AlumniCertificate $certificate)
    {
        Gate::authorize('delete', $certificate);

        $image = $certificate->certificate_image;
        $certificate->delete();
        PrivateUploads::delete('certificates', $image);

        return to_route('profile')->with('status', 'Certificate deleted successfully.');
    }
}
