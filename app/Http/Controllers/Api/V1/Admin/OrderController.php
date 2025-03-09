<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User; // Assuming orders are linked to users
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Constructor to apply middleware.
     * In a real application, you'd likely have an 'admin' middleware group.
     */
    public function __construct()
    {
        // Example: $this->middleware('auth:sanctum');
        // Example: $this->middleware('can:manage-orders'); // Using Laravel Gates/Policies
    }

    /**
     * Display a listing of the orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Order::with(['user:id,name,email', 'orderItems.product:id,name,price,slug']);

            // Filtering
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }
            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('order_number', 'like', $searchTerm)
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'like', $searchTerm)
                                    ->orWhere('email', 'like', $searchTerm);
                      });
                });
            }
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date') . ' 23:59:59']);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 10);
            $orders = $query->paginate($perPage);

            return response()->json($orders, Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error("Failed to fetch orders: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to fetch orders.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        try {
            $order->load(['user:id,name,email', 'orderItems.product:id,name,price,slug,image_url', 'payment']);

            return response()->json($order, Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error("Failed to fetch order {$order->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to fetch order.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order)
    {
        try {
            $validatedData = $request->validate([
                'status' => ['sometimes', 'required', Rule::in(Order::STATUSES)], // Assuming Order model has a STATUSES constant
                'shipping_address' => 'sometimes|required|string|max:255',
                'billing_address' => 'sometimes|required|string|max:255',
                'tracking_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                // Add other updatable fields as needed
            ]);

            DB::beginTransaction();

            $order->fill($validatedData);

            // If status is being updated, perform specific logic
            if ($request->has('status') && $order->isDirty('status')) {
                // Example: If status changes to 'shipped', set shipped_at timestamp
                if ($order->status === 'shipped' && is_null($order->shipped_at)) {
                    $order->shipped_at = now();
                }
                // Example: If status changes to 'delivered', set delivered_at timestamp
                if ($order->status === 'delivered' && is_null($order->delivered_at)) {
                    $order->delivered_at = now();
                }
                // Example: If status changes to 'cancelled', handle inventory or refunds
                if ($order->status === 'cancelled') {
                    // This is where you might integrate with a refund service or update inventory.
                    // For instance, dispatch an event: event(new OrderCancelled($order));
                    // Or call a service: (new InventoryService())->restoreStock($order);
                    Log::info("Order {$order->order_number} status changed to cancelled. Further actions (e.g., refund, inventory restore) might be needed.");
                }

                // Dispatch an event for order status update
                // event(new OrderStatusUpdated($order));
            }

            $order->save();

            DB::commit();

            $order->load(['user:id,name,email', 'orderItems.product:id,name,price,slug,image_url', 'payment']); // Reload with relationships

            return response()->json([
                'message' => 'Order updated successfully.',
                'order' => $order
            ], Response::HTTP_OK);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update order {$order->id}: " . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
            return response()->json([
                'message' => 'Failed to update order.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        try {
            // In a production e-commerce system, orders are rarely hard-deleted.
            // Instead, they are usually soft-deleted (if the Order model uses SoftDeletes trait)
            // or their status is changed to 'cancelled' or 'archived'.
            // For this example, we'll assume SoftDeletes is enabled on the Order model.
            // If not, this would be a hard delete.

            DB::beginTransaction();

            $orderNumber = $order->order_number;
            $order->delete(); // This will soft delete if SoftDeletes trait is used

            DB::commit();

            return response()->json([
                'message' => "Order {$orderNumber} deleted successfully (soft-deleted if applicable)."
            ], Response::HTTP_NO_CONTENT); // HTTP_NO_CONTENT is appropriate for successful deletion with no content to return

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order not found.'
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete order {$order->id}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to delete order.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Placeholder for integrating with the Microservices-based Analytics Dashboard.
     * This method would typically be in an AnalyticsController, but demonstrates cross-project context.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderAnalytics(Request $request)
    {
        // This would typically involve making an HTTP request to the Analytics Dashboard microservice.
        // Example:
        // $analyticsServiceUrl = config('services.analytics.url');
        // $response = Http::get("{$analyticsServiceUrl}/api/v1/orders/summary", $request->all());
        // if ($response->successful()) {
        //     return response()->json($response->json(), Response::HTTP_OK);
        // }
        // Log::error("Failed to fetch order analytics from external service.", ['response' => $response->body()]);
        // return response()->json(['message' => 'Could not retrieve order analytics.'], Response::HTTP_SERVICE_UNAVAILABLE);

        // For now, a mock response or simple aggregation:
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        return response()->json([
            'message' => 'Order analytics summary (local aggregation, for full analytics consult external service).',
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'total_revenue' => number_format($totalRevenue, 2),
            'note' => 'For detailed and historical analytics, integrate with the Microservices-based Analytics Dashboard.'
        ], Response::HTTP_OK);
    }
}