<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic title, falls back to app.name config --}}
    <title>{{ config('app.name', 'E-commerce Platform') }}</title>

    {{-- Preconnect to Google Fonts for performance --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    {{-- Load default Laravel fonts --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite entry point for CSS and JS. This will automatically inject the compiled assets. --}}
    {{-- 'resources/sass/app.scss' is the main stylesheet for the application. --}}
    {{-- 'resources/js/app.ts' is the main TypeScript entry point for the Vue application. --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased">
    {{-- No-script message for users with JavaScript disabled --}}
    <noscript>
        <strong>We're sorry but this application requires JavaScript to be enabled. Please enable it to continue.</strong>
    </noscript>

    {{-- The main div where the Vue application will be mounted --}}
    <div id="app">
        {{-- Vue components will render here --}}
    </div>

    {{--
        Pass Laravel/PHP configuration variables to the client-side JavaScript.
        This is a common pattern to make environment variables and dynamic server-side
        data available to the Vue.js application without making additional API calls
        on initial load.
    --}}
    <script>
        window.AppConfig = {
            appName: '{{ config('app.name') }}',
            csrfToken: '{{ csrf_token() }}',
            // Base URL for the API endpoints of this e-commerce platform
            apiBaseUrl: '{{ url('/api/v1') }}',

            // Cross-Project Context: URLs for interconnected microservices
            // Example: URL for the Microservices-based Analytics Dashboard
            analyticsServiceUrl: '{{ env('ANALYTICS_SERVICE_URL', 'http://localhost:8002') }}',
            // Example: URL for the AI-Powered Document Categorizer & Search (if used for product search/filtering)
            categorizationServiceUrl: '{{ env('CATEGORIZATION_SERVICE_URL', 'http://localhost:8003') }}',
            // Example: URL for the Peer-to-Peer Encrypted Chat Application (for customer support integration)
            chatServiceUrl: '{{ env('CHAT_SERVICE_URL', 'http://localhost:8004') }}',
            // Example: URL for the Developer Portfolio Aggregator & Showcase (if integrating developer profiles for vendors)
            portfolioServiceUrl: '{{ env('PORTFOLIO_SERVICE_URL', 'http://localhost:8005') }}',

            // Payment Gateway Public Keys (sensitive keys should be handled securely,
            // public keys are generally safe to expose client-side)
            stripePublicKey: '{{ env('STRIPE_KEY') }}',
            paypalClientId: '{{ env('PAYPAL_CLIENT_ID') }}',

            // Add any other global configurations needed by the frontend
            // e.g., feature flags, default settings, etc.
            debugMode: {{ config('app.debug') ? 'true' : 'false' }},
        };
    </script>
</body>
</html>