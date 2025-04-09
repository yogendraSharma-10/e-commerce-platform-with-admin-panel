<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- Authentication Routes ---
// Laravel's built-in authentication routes. These provide server-rendered views
// for login, registration, password reset, and email verification.
// While the frontend (Vue.js) might handle authentication via API calls,
// these routes provide a robust fallback or initial entry point for traditional
// browser-based authentication flows.
Auth::routes(['verify' => true]); // 'verify' enables email verification routes

// Redirect authenticated users from the default Laravel `/home` route to the SPA root.
// The actual "dashboard" or post-login experience will be handled by Vue Router.
Route::get('/home', function () {
    return redirect('/');
})->middleware('auth')->name('home');

// --- Main Single Page Application (SPA) Entry Point ---
// This route serves the main 'resources/views/app.blade.php' view.
// This Blade file acts as the entry point for the Vue.js application,
// which then takes over client-side routing, state management, and UI rendering.
// All public-facing e-commerce shop pages and the admin panel will be rendered
// within this single application shell by Vue Router.
Route::get('/', function () {
    return view('app');
})->name('spa.entry');

// --- Catch-all Route for Vue Router ---
// This is a critical route for Single Page Applications (SPAs).
// It catches any web route that hasn't been explicitly defined above (e.g., /products,
// /admin/orders, /checkout, /product/123). For any such URL, it serves the
// 'app.blade.php' view. This allows Vue Router to then take over and handle
// the client-side routing for these paths, ensuring deep linking works correctly
// without Laravel trying to find a server-side route for every possible client-side path.
// This route must be the last one defined in this file to ensure specific routes
// (like authentication routes) are matched first.
Route::any('{any}', function () {
    return view('app');
})->where('any', '.*')->name('spa.fallback');

// --- Cross-Project Integration Placeholders (Optional) ---
// In a microservices architecture, this e-commerce platform might need to link to
// or integrate with other services. These commented-out routes illustrate how
// such external service interactions could be handled, typically by redirecting
// to the external service's UI or by serving a view that embeds content from it.
// The URLs for these external services would be configured in the .env file.

/*
// Example: Link to the Microservices-based Analytics Dashboard
// This route could redirect administrators to a dedicated analytics service UI.
Route::get('/admin/external-analytics', function () {
    // Ensure ANALYTICS_DASHBOARD_URL is set in your .env file.
    return redirect(env('ANALYTICS_DASHBOARD_URL', 'http://localhost:8002'));
})->middleware(['auth', 'can:view-analytics'])->name('admin.external_analytics');

// Example: Link to the Developer Portfolio Aggregator for a specific user
// This could allow admins to view a user's aggregated portfolio from within the e-commerce admin.
Route::get('/admin/user-portfolio/{userId}', function ($userId) {
    // Ensure PORTFOLIO_AGGREGATOR_URL is set in your .env file.
    return redirect(env('PORTFOLIO_AGGREGATOR_URL', 'http://localhost:8003') . '/users/' . $userId);
})->middleware(['auth', 'can:manage-users'])->name('admin.user_portfolio');

// Example: Link to the AI-Powered Document Categorizer & Search
// If the e-commerce platform had extensive documentation or content, this could link to an external tool.
Route::get('/admin/document-categorizer', function () {
    // Ensure DOCUMENT_CATEGORIZER_URL is set in your .env file.
    return redirect(env('DOCUMENT_CATEGORIZER_URL', 'http://localhost:8004'));
})->middleware(['auth', 'can:manage-content'])->name('admin.document_categorizer');
*/