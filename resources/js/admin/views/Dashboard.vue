<template>
  <div class="admin-dashboard p-6 bg-gray-100 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>

    <div v-if="loading" class="text-center py-8">
      <div class="spinner-border animate-spin inline-block w-8 h-8 border-4 rounded-full text-blue-500" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-gray-600 mt-2">Loading dashboard data...</p>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline"> {{ error }}</span>
    </div>

    <div v-else>
      <!-- Key Metrics Section -->
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Sales</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ formatCurrency(dashboardStats.totalSales) }}</p>
          </div>
          <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.592 1L21 12m-6-4h4.586M12 18c-1.11 0-2.08-.402-2.592-1L3 12m6 4H4.414" />
          </svg>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Orders</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ dashboardStats.totalOrders }}</p>
          </div>
          <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Products</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ dashboardStats.totalProducts }}</p>
          </div>
          <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
          </svg>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">New Customers (30 days)</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ dashboardStats.newCustomers }}</p>
          </div>
          <svg class="h-8 w-8 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H2v-2a3 3 0 015.356-1.857M9 20v-2m3 2v-2m3 2v-2M9 10a6 6 0 016 6v2a2 2 0 002 2h2a2 2 0 002-2v-2a6 6 0 01-6-6V6a2 2 0 00-2-2H9a2 2 0 00-2 2v4a6 6 0 016 6z" />
          </svg>
        </div>
      </section>

      <!-- Recent Orders Section -->
      <section class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Orders</h2>
        <div v-if="recentOrders.length === 0" class="text-gray-600">No recent orders found.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">View</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in recentOrders" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ order.customerName }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatCurrency(order.total) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="getStatusBadgeClass(order.status)">
                    {{ order.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(order.orderDate) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <router-link :to="{ name: 'admin.orders.detail', params: { id: order.id } }" class="text-indigo-600 hover:text-indigo-900">View</router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Quick Actions / Navigation -->
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link :to="{ name: 'admin.products' }" class="block bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-200">
          <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          <p class="text-lg font-semibold text-gray-800">Manage Products</p>
          <p class="text-sm text-gray-500 mt-1">Add, edit, and remove products.</p>
        </router-link>

        <router-link :to="{ name: 'admin.orders' }" class="block bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-200">
          <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          <p class="text-lg font-semibold text-gray-800">Manage Orders</p>
          <p class="text-sm text-gray-500 mt-1">Process and track customer orders.</p>
        </router-link>

        <router-link :to="{ name: 'admin.analytics' }" class="block bg-white rounded-lg shadow-md p-6 text-center hover:shadow-lg transition-shadow duration-200">
          <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M18 14v4.714A2.286 2.286 0 0115.714 21H5.286A2.286 0 013 18.714V8.286A2.286 0 015.286 6h4.714M12 10V4.714A2.286 2.286 0 0114.286 2h4.714A2.286 0 0121 4.714v4.714A2.286 2.286 0 0118.714 12H12z" />
          </svg>
          <p class="text-lg font-semibold text-gray-800">View Analytics</p>
          <p class="text-sm text-gray-500 mt-1">Gain insights into sales and customer behavior.</p>
          <!-- Cross-project context