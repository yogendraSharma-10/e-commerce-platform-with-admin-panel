<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for interacting with various payment gateways.
 * This class abstracts the complexities of different payment providers,
 * offering a unified interface for processing payments, creating intents,
 * confirming payments, and handling refunds.
 */
class PaymentGatewayService
{
    /**
     * The default payment gateway to use, configured via environment variables.
     *
     * @var string
     */
    protected string $defaultGateway;

    /**
     * PaymentGatewayService constructor.
     * Initializes the default payment gateway based on configuration.
     */
    public function __construct()
    {
        