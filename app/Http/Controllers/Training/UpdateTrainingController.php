<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateTrainingRequest;
use App\Models\Training;
use App\Support\PrivateUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final class UpdateTrainingController extends Controller
{
    public function __invoke(UpdateTrainingRequest $request, Training $training)
    {
        Gate::authorize('update', $training);
        $data = $request->safe()->except('image');
        $oldImage = $training->image;
        $newImage = null;
        if ($file = $request->file('image')) {
            $newImage = 'training_'.Str::uuid().'.'.$file->extension();
            abort_unless(PrivateUploads::store($file, 'trainings', $newImage), 500, 'Image upload failed.');
            $data['image'] = $newImage;
        }

        try {
            $training->update($data);
        } catch (\Throwable $exception) {
            PrivateUploads::delete('trainings', $newImage);
            throw $exception;
        }
        if ($newImage) {
            PrivateUploads::delete('trainings', $oldImage);
        }

        return to_route('admin.trainings_edit', ['id' => $training->id])->with('status', 'Training updated successfully.');
    }

    public function legacy(UpdateTrainingRequest $request)
    {
        return $this($request, Training::findOrFail($request->integer('id')));
    }
}
