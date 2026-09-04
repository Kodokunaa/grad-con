<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DestroyTrainingController extends Controller
{
    public function __invoke(Training $training)
    {
        Gate::authorize('delete', $training);

        DB::transaction(fn () => $training->delete());
        PrivateUploads::delete('trainings', $training->image);

        return to_route('admin.trainings_list')->with('status', 'Training deleted.');
    }
}
