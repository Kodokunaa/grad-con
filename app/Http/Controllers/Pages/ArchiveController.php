<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class ArchiveController extends Controller
{
    public function __invoke(Request $request)
    {
        return redirect('/alumni_officer/archive.php');
    }
}
