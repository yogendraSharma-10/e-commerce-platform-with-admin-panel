<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCategorizationService; // Service for advanced product categorization
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Event; // For cross-project analytics integration
use Illuminate\Support\Facades\Log; // For logging cross-project interactions

class ProductController extends Controller
{
    /**
     * The product categorization service instance.
     * This service could be used for automatically assigning categories, tags,
