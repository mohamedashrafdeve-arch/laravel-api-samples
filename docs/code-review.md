# Code review: `UserController@update`

Before: [`examples/UserController.before.php`](../examples/UserController.before.php)
After: [`app/Http/Controllers/Api/UserController.php`](../app/Http/Controllers/Api/UserController.php)

## Issues

| # | Issue | Impact | Fix |
|---|---|---|---|
| 1 | No authorization + `$request->all()` | Any logged-in user can edit any account and set `is_admin`/`role` | Policy via FormRequest `authorize()`, `$request->validated()`, strict `$fillable` |
| 2 | Raw SQL with `$id` concatenated | SQL injection (`1 OR 1=1`) | Eloquent relation (bound parameters) |
| 3 | `User::find()` without a null check | 500 instead of 404 | Route model binding |
| 4 | Full model returned, unbounded `SELECT *` | Leaks password hash/tokens; heavy responses | `UserResource`, `$hidden`, `limit` |
| 5 | No validation | Bad or duplicate data stored | `UpdateUserRequest` rules |

## Highest priority: #1

Broken authorization combined with mass assignment needs no special skill to exploit: a normal request with another user's ID, or an extra `is_admin=1` field, gives account takeover or privilege escalation. SQL injection (#2) is a close second and is fixed in the same patch.
