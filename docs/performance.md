# Performance target

The production container is configured for a target of 100 requests per second on cached and lightweight routes. It uses 16 Apache workers, OPcache, compressed responses, long-lived static asset caching, cookie sessions, bounded feed queries, short-lived shared caches, and a background database queue worker for email.

This target is capacity guidance rather than a guarantee. Actual throughput depends on Render CPU and memory, Aiven latency and connection limits, response size, and the mix of reads, writes, uploads, and email-triggering requests. Render's free service also sleeps when idle, so exclude the cold-start request from measurements. A sustained 100 requests per second normally requires an always-on paid instance and load testing from a host near the Render region.

After deployment, warm the service and test a non-mutating endpoint from an authorized environment:

```bash
ab -n 5000 -c 100 -k https://your-service.onrender.com/up
```

Test an authenticated page separately using a dedicated test account and cookie. Do not benchmark create, update, email, or upload endpoints because those requests change data or consume third-party quotas. Watch Render memory/CPU and Aiven active connections during the run. Reduce `MaxRequestWorkers` if memory approaches the instance limit; scale the Render instance when CPU is saturated.

The feed returns the newest `PERFORMANCE_FEED_LIMIT` events and caches its shared data for `PERFORMANCE_FEED_CACHE_SECONDS`. Directory data uses `PERFORMANCE_DIRECTORY_CACHE_SECONDS`. Event, job, account, reaction, and comment mutations clear their corresponding cache where immediate freshness matters.
