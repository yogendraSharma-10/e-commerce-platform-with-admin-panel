<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User; // Assuming User model exists for order relationships

/**
 * @OA\Tag(
 *     name="Analytics",
 *     description="API Endpoints for E-commerce Analytics"
 * )
 */
class AnalyticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/analytics/sales-overview",
