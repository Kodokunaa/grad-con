<?php

namespace App\Support;

final class PageResponse extends \RuntimeException
{
    public function __construct(public readonly mixed $body = '')
    {
        parent::__construct('Page response');
    }
}
