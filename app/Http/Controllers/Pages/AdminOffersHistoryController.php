<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use App\Models\EmployerActivityLog;
use Illuminate\Http\Request;

final class AdminOffersHistoryController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $error = '';
            $logs = [];
            $employerCount = 0;
            $alumniCount = 0;
            $logModels = EmployerActivityLog::query()->with(['employer', 'alumni', 'offer'])->latest('created_at')->limit(500)->get();
            $logs = $logModels->map(function ($log) {
                $row = $log->toArray();
                $row['employer_name'] = $log->employer?->fullname;
                $row['alumni_name'] = $log->alumni?->fullname;
                $row['alumni_email'] = $log->alumni?->email;
                $row['offer_status'] = $log->offer?->status?->value ?? $log->offer?->status;
                $row['accepted_at'] = $log->offer?->accepted_at;
                $row['declined_at'] = $log->offer?->declined_at;

                return $row;
            })->all();
            $searchCount = $logModels->where('action', 'SEARCH_ALUMNI')->count();
            $offerCount = $logModels->where('action', 'JOB_OFFER_SENT')->count();
            $employerCount = $logModels->pluck('employer_id')->filter()->unique()->count();
            $alumniCount = $logModels->pluck('alumni_id')->filter()->unique()->count();
            echo view('partials.header', \get_defined_vars());
            echo view('partials.admin_sidebar', \get_defined_vars());

            return $this->pageView('pages.admin.offers_history', get_defined_vars());
        });
    }
}
