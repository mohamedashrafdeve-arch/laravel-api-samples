# Laravel API Samples

Code samples for common backend scenarios: a secure paginated list endpoint, query/index optimization, production 500 troubleshooting, and a security-focused code review.

The files follow a standard Laravel project layout (Laravel 11+, Sanctum), so they can be dropped into an existing app.

## Contents

| Topic | Files |
|---|---|
| Paginated + searchable list endpoint with validation and authorization | `routes/api.php`, `app/Http/Requests/ListOrdersRequest.php`, `app/Http/Controllers/Api/OrderController.php`, `app/Models/Order.php`, `app/Policies/OrderPolicy.php`, `app/Http/Resources/OrderResource.php` |
| Slow API (1s → 8s): diagnosis, index and query fix | `docs/slow-query.md`, `database/migrations/2026_11_18_000000_add_status_created_index_to_orders.php` |
| 500 in production only: first five checks | `docs/production-500.md` |
| Code review: vulnerable endpoint vs fixed endpoint | `docs/code-review.md`, `examples/UserController.before.php`, `app/Http/Controllers/Api/UserController.php`, `app/Http/Requests/UpdateUserRequest.php`, `app/Policies/UserPolicy.php`, `app/Http/Resources/UserResource.php` |

## Key decisions

- **Validation lives in FormRequests.** Controllers only ever use `$request->validated()`.
- **Authorization is layered:** `auth:sanctum` (401) → Policy (403) → tenant query scope (`visibleTo`), so data from other tenants can't leak even with crafted filters.
- **Sort columns are whitelisted.** User input never reaches `orderBy()` as a raw column name.
- **API Resources control output.** Models are never returned directly.
- **Composite index + cursor pagination** for large tables: the cost stays constant regardless of page depth.

## Usage

Register the policies if they aren't auto-discovered, add the `orders` and `customer` relations shown in the models, and run:

```bash
php artisan migrate
```
