<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// use App\Http\Resources\V1\OrderResource; // For standardized API responses
// use App\Services\AnalyticsService; // For cross-project integration with Microservices-based Analytics Dashboard

/**
 * @OA\Tag(
 *     name="Admin Orders",
 *     description="API Endpoints for managing orders in the admin panel"
 * )
 */
class OrderController extends Controller
{
    // /**
    //  * @var AnalyticsService
    //  */
    // protected $analyticsService;

    // /**
    //  * Inject AnalyticsService for cross-project communication.
    //  *
    //  * @param AnalyticsService $analyticsService
    //  */
    // public function __construct(AnalyticsService $analyticsService)
    // {
    //     $this->analyticsService = $analyticsService;
    // }

    /**
     * @OA\Get(
     *      path="/api/v1/admin/orders",
     *      operationId="getAdminOrdersList",
     *      tags={"Admin Orders"},
     *      summary="Get list of all orders for admin",
     *      description="Returns a paginated list of orders, with optional filtering and search.",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of orders per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter orders by status (e.g., pending, processing, shipped, delivered, cancelled, refunded)",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search orders by order number, user name, or user email",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Orders retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *              @OA\Property(property="meta", type="object",
     *                  @OA\Property(property="current_page", type="integer"),
     *                  @OA\Property(property="last_page", type="integer"),
     *                  @OA\Property(property="total", type="integer")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      )
     * )
     *
     * Display a listing of the orders for the admin panel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Implement authorization check, e.g., using Laravel Gates or Policies:
        // $this->authorize('viewAny', Order::class);

        $perPage = $request->get('per_page', 15);
        $status = $request->get('status');
        $search = $request->get('search');

        try {
            $orders = Order::query()
                ->when($status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($search, function ($query, $search) {
                    $query->where('order_number', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($q) use ($search) {
                              $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");