<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEducationRequest;
use App\Models\AlumniEducation;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\DB;

final class StoreEducationController extends Controller
{
    public function __invoke(StoreEducationRequest $request)
    {
        DB::transaction(function () use ($request) {
            $education = new AlumniEducation;
            $education->forceFill(array_merge($request->validated(), ['user_id' => $request->user()->id]))->save();
            $log = new SecurityLog;
            $log->forceFill(['user_id' => $request->user()->id, 'action' => 'EDUCATION_ADDED', 'details' => 'Added educational background: '.$education->school_name])->save();
        });

        return to_route('alumni.add_degree')->with('status', 'Educational background added successfully.');
    }
}
