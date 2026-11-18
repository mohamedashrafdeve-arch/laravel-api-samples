# API slowed from <1s to 8s

## Sample

`orders (id, user_id, status, total, created_at, ...)`: ~3M rows, indexes only on `id` and `user_id`.

```sql
SELECT * FROM orders
WHERE status = 'paid'
  AND DATE(created_at) >= '2026-01-01'
ORDER BY created_at DESC
LIMIT 20 OFFSET 60000;
```

## What I inspect, in order

1. **What changed:** recent deploy, data growth, traffic. Use APM / Telescope / MySQL slow query log to confirm the time is spent in the DB, not in PHP (N+1, external calls).
2. **`EXPLAIN ANALYZE`:** look for `type=ALL`, a large `rows` estimate, `Using filesort`, `Using temporary`.
3. **Query count per request:** hundreds of small queries means N+1; fix with eager loading.

## Problems

- `DATE(created_at)` wraps the column in a function, so no index can be used (not sargable).
- No index on `status` / `created_at`: full scan plus a filesort over 3M rows.
- `OFFSET 60000` reads and discards 60k rows on every call and gets slower as data grows.
- `SELECT *` pulls unneeded (possibly large) columns.

## Fix

```sql
ALTER TABLE orders ADD INDEX idx_orders_status_created (status, created_at);

SELECT id, user_id, status, total, created_at
FROM orders
WHERE status = 'paid'
  AND created_at >= '2026-01-01 00:00:00'
  AND created_at < :last_seen_created_at   -- keyset pagination
ORDER BY created_at DESC
LIMIT 20;
```

Laravel:

```php
Order::select(['id', 'user_id', 'status', 'total', 'created_at'])
    ->where('status', 'paid')
    ->where('created_at', '>=', '2026-01-01')
    ->orderByDesc('created_at')
    ->orderByDesc('id')
    ->cursorPaginate(20);
```

## Why it helps

- The composite index (equality first, range/sort second) lets MySQL seek straight to `status='paid'`, read rows already sorted by `created_at`, and stop after 20. No scan, no filesort.
- A plain range predicate keeps the condition index-friendly.
- Cursor pagination costs the same on page 1 and page 3,000.
- Fewer columns means less I/O and memory.

Verify with `EXPLAIN` (index used, small `rows`), compare timings before and after, and keep the slow query log on to catch regressions.
