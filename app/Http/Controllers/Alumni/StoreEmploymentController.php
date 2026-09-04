<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmploymentRequest;
use App\Models\EmploymentHistory;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\DB;

final class StoreEmploymentController extends Controller
{
    public function __invoke(StoreEmploymentRequest $request)
    {
        $current = ! $request->filled('end_date');
        DB::transaction(function () use ($request, $current) {
            if ($current) {
                EmploymentHistory::where('user_id', $request->user()->id)->whereNull('end_date')->update(['end_date' => $request->date('start_date')->subDay()->toDateString()]);
            } $employment = new EmploymentHistory;
            $employment->forceFill(array_merge($request->validated(), ['user_id' => $request->user()->id]))->save();
            $request->user()->forceFill(['employment_status' => $current ? 'Employed' : ($request->user()->employmentHistory()->whereNull('end_date')->exists() ? 'Employed' : 'Unemployed')])->save();
            $log = new SecurityLog;
            $log->forceFill(['user_id' => $request->user()->id, 'action' => 'EMPLOYMENT_HISTORY_ADDED', 'details' => 'Employment history added', 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255)])->save();
        });

        return to_route('alumni.employment_history')->with('status', $current ? 'Current employment added successfully.' : 'Employment history added successfully.');
    }
}
