<?php

namespace App\Http\Controllers;

abstract class PageController extends Controller
{
    protected function renderPage(callable $callback)
    {
        $level = ob_get_level();
        ob_start();
        try {
            $result = $callback();
            $body = ob_get_clean().(is_string($result) ? $result : '');
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return response($body);
    }

    protected function pageView(string $name, array $data): string
    {
        unset($data['__env'], $data['app']);

        return view($name, $data)->render();
    }
}
