<?php

namespace App\Http\Controllers;

use App\Support\PageContext;
use App\Support\PageResponse;

abstract class PageController extends Controller
{
    protected function renderPage(callable $callback)
    {
        $context = app(PageContext::class);
        $level = ob_get_level();
        ob_start();
        try {
            $result = $callback();
            $body = ob_get_clean().(is_string($result) ? $result : '');
        } catch (PageResponse $response) {
            $body = ob_get_clean().(string) $response->body;
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return response($body, $context->status, $context->headers);
    }

    protected function pageView(string $name, array $data): string
    {
        unset($data['__env'], $data['app']);

        return view($name, $data)->render();
    }
}
