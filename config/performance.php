<?php

return [
    'feed_limit' => (int) env('PERFORMANCE_FEED_LIMIT', 30),
    'feed_cache_seconds' => (int) env('PERFORMANCE_FEED_CACHE_SECONDS', 5),
    'directory_cache_seconds' => (int) env('PERFORMANCE_DIRECTORY_CACHE_SECONDS', 30),
];
