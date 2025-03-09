<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Analytics",
 *     description="API Endpoints for E-commerce Analytics and Reporting"
 * )
 */
class AnalyticsController extends Controller
{
    /**
     * Constructor for AnalyticsController.
     * Apply middleware for authentication and authorization to ensure only authorized users
     * (e.g., administrators) can access these analytics endpoints.
     */
    