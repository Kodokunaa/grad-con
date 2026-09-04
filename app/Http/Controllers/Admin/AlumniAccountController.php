<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAlumniRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class AlumniAccountController extends Controller
{
    public function update(UpdateAlumniRequest $request, User $alumni): RedirectResponse
    {
        abort_unless($alumni->role === 'alumni', 404);
        Gate::authorize('update', $alumni);
        $data = $request->validated();
        $alumni->forceFill(['fullname' => $data['fullname'], 'email' => $data['email'] ?: null, 'course' => $data['course'] ?: null, 'batch_year' => $data['batch_year'] ?: null, 'is_active' => $request->boolean('is_active')]);
        if (! empty($data['password'])) {
            $alumni->password = $data['password'];
        }
        $alumni->save();
        return to_route('admin.alumni_edit', ['id' => $alumni->id])->with('status', 'Updated successfully!');
    }

    public function destroy(User $alumni): RedirectResponse
    {
        abort_unless($alumni->role === 'alumni', 404);
        Gate::authorize('delete', $alumni);
        $alumni->delete();
        return to_route('admin.alumni_list')->with('status', 'Alumni deleted successfully.');
    }
}
