<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListOrdersRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(ListOrdersRequest $request): AnonymousResourceCollection
    {
        $v = $request->validated();

        $orders = Order::query()
            ->visibleTo($request->user())
            ->with('customer:id,name')
            ->when($v['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($v['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->orderBy($v['sort'] ?? 'created_at', $v['direction'] ?? 'desc')
            ->paginate($v['per_page'] ?? 20)
            ->withQueryString();

        return OrderResource::collection($orders);
    }
}
