<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Route model binding → 404 for a missing user.
     * UpdateUserRequest → policy check (403) + validation (422).
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $user->update($request->validated());

        return new UserResource(
            $user->load(['orders' => fn ($q) => $q->latest()->limit(20)])
        );
    }
}
