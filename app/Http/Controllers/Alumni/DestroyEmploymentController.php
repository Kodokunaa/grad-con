<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\EmploymentHistory;
use App\Models\SecurityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DestroyEmploymentController extends Controller
{
    public function __invoke(EmploymentHistory $employment)
    {
        Gate::authorize('delete', $employment);
        $userId = $employment->user_id;

        DB::transaction(function () use ($employment, $userId): void {
            $employment->delete();
            $employed = EmploymentHistory::where('user_id', $userId)->whereNull('end_date')->exists();
            User::whereKey($userId)->update(['employment_status' => $employed ? 'Employed' : 'Unemployed']);
            $log = new SecurityLog;
            $log->forceFill([
                'user_id' => $userId,
                'action' => 'EMPLOYMENT_HISTORY_DELETED',
                'details' => 'Employment history deleted',
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ])->save();
        });

        return to_route('alumni.employment_history')->with('status', 'Employment history deleted successfully.');
    }
}
