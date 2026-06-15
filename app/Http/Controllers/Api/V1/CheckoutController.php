<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User; // Assuming User model has cartItems relationship
use App\Services\PaymentGatewayService;
use App\Services\AnalyticsService; // For cross-project context: Microservices-based Analytics Dashboard
use Exception;

class CheckoutController extends Controller
{
    protected PaymentGatewayService $