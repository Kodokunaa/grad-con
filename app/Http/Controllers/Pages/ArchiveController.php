<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\PageController;
use Illuminate\Http\Request;

final class ArchiveController extends PageController
{
    public function __invoke(Request $request)
    {
        return $this->renderPage(function () {
            $pdo = gc_context()->pdo();
            \gc_header('Location: '.rtrim('', '/').'/alumni_officer/archive.php');
            \gc_finish();

            return $this->pageView('pages.archive', get_defined_vars());
        });
    }
}
