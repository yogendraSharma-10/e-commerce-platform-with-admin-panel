<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use RuntimeException;
use Exception;
use Log;

/**
 * Service for suggesting product categories using an external AI-powered categorization service.
 * This service integrates with the "AI-Powered Document Categorizer & Search" microservice.
 */
class ProductCategorizationService
{
    /**
     * The base URL for the AI Categorizer Service.
     *
     * @var string
     */
    protected string $aiServiceUrl;

    /**
     * The API key for the AI Categorizer Service.
     *
     * @var string|null
     */
    protected ?string $aiServiceApiKey;

    /**
     * Create a new ProductCategorizationService instance.
     *
     * @throws RuntimeException If the AI Categorizer Service URL is not configured.
     */
    public function __construct()
    {
        // Retrieve configuration from services.php, which in turn reads from .env
        $this->aiServiceUrl = config('services.ai_categorizer.url');
        $this->aiServiceApiKey = config('services.ai_categorizer.api_key');

        if (empty($this->aiServiceUrl)) {
            throw new RuntimeException('AI Categorizer Service URL is not configured in config/services.php or .env.');
        }
    }

    /**
     * Suggests categories for a given product by sending its details to an external AI service.
     *
     * @param Product $product The product model instance to categorize.
     * @return Collection<string> A collection of suggested category names.
     * @throws Exception If the AI service request fails, times out, or returns an invalid response.
     */
    public function suggestCategories(Product $product): Collection
    {
        // Prepare the data payload for the AI categorization service.
        // We send relevant product attributes that can help the AI determine categories.
        $data = [
            'product_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            // Add other relevant product attributes if available and useful for categorization,
            // e.g., 'brand', 'tags', 'material', 'features'.
            // For example: 'attributes' => $product->attributes->pluck('value', 'name')->toArray(),
        ];

        try {
            // Initialize the HTTP client with a timeout to prevent long-running requests.
            $request = Http::timeout(15); // 15-second timeout

            // If an API key is configured, add it to the request headers for authentication.
            if ($this->aiServiceApiKey) {
                $request->withHeaders([
                    'X-API-KEY' => $this->aiServiceApiKey,
                    'Accept' => 'application/json',
                ]);
            }

            // Make a POST request to the AI Categorizer Service's categorization endpoint.
            // Assuming the AI service exposes an endpoint like /api/v1/categorize.
            $response = $request->post("{$this->aiServiceUrl}/api/v1/categorize", $data);

            // Laravel's HTTP client can automatically throw an exception for 4xx or 5xx responses.
            $response->throw();

            $responseData = $response->json();

            // Validate the structure of the response from the AI service.
            // We expect a 'categories' key containing an array of strings.
            if (!isset($responseData['categories']) || !is_array($responseData['categories'])) {
                throw new Exception(
                    'Invalid response structure from AI Categorizer Service: missing or invalid "categories" field.',
                    $response->status()
                );
            }

            // Return the suggested categories as a Laravel Collection.
            return collect($responseData['categories']);

        } catch (RequestException $e) {
            // Log detailed error information for HTTP client exceptions (e.g., connection errors, 4xx/5xx responses).
            Log::error("AI Categorizer Service request failed for product ID {$product->id}: " . $e->getMessage(), [
                'product_id' => $product->id,
                'response_body' => $e->response ? $e->response->body() : 'N/A',
                'status' => $e->response ? $e->response->status() : 'N/A',
                'service_url' => $this->aiServiceUrl,
            ]);
            // Re-throw a more user-friendly exception.
            throw new Exception('Failed to get categories from AI service due to network or service error.', 0, $e);
        } catch (Exception $e) {
            // Catch any other unexpected exceptions during the process (e.g., JSON decoding errors, validation errors).
            Log::error("An unexpected error occurred during AI categorization for product ID {$product->id}: " . $e->getMessage(), [
                'product_id' => $product->id,
                'service_url' => $this->aiServiceUrl,
            ]);
            throw new Exception('An unexpected error occurred during product categorization.', 0, $e);
        }
    }
}