<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EmployerAlumniListController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $course = trim((string) $request->query('course', ''));
        $batch = trim((string) $request->query('batch_year', ''));

        $query = User::query()->where('role', 'alumni')->where('is_active', true)->where('status', 'approved')
            ->select(['id', 'fullname', 'email', 'course', 'batch_year', 'career_objective', 'skills', 'work_experience', 'employment_status'])
            ->with([
                'education' => fn ($builder) => $builder->orderByDesc('end_year')->limit(5),
                'certificates' => fn ($builder) => $builder->orderByDesc('issue_date')->limit(5),
                'employmentHistory' => fn ($builder) => $builder->orderByDesc('start_date')->limit(5),
                'degrees' => fn ($builder) => $builder->orderByDesc('id')->limit(5),
            ]);
        if ($search !== '') {
            $query->where(fn ($builder) => $builder->where('fullname', 'like', "%{$search}%")
                ->orWhere('skills', 'like', "%{$search}%"));
        }
        if ($course !== '') {
            $query->where('course', $course);
        }
        if ($batch !== '') {
            $query->where('batch_year', $batch);
        }

        $alumni = $query->orderBy('fullname')->paginate(24)->withQueryString();
        $courses = User::query()->where('role', 'alumni')->where('is_active', true)->whereNotNull('course')->where('course', '<>', '')->distinct()->orderBy('course')->pluck('course');
        $batches = User::query()->where('role', 'alumni')->where('is_active', true)->whereNotNull('batch_year')->where('batch_year', '<>', '')->distinct()->orderByDesc('batch_year')->pluck('batch_year');

        return view('pages.employer.alumni_list', compact('alumni', 'courses', 'batches', 'search', 'course', 'batch'));
    }
}
