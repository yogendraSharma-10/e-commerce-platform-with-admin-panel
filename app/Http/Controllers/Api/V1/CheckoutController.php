<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart; // Assuming a Cart model exists
use App\Models\CartItem; // Assuming a CartItem model exists
use App\Services\PaymentGatewayService;
// Cross-project context: Analytics integration
// use App\Services\AnalyticsService; // If a dedicated service exists for the Microservices-