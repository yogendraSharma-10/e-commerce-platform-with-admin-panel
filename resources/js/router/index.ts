import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '../store'; // Assuming a Pinia/Vuex store for auth

// Define routes for the application
const routes: Array<RouteRecordRaw> = [
    // Public Shop Routes
    {
        path: '/',
        name: 'home',
        component: () => import('../shop/views/Home.vue'), // Main product listing or landing page
        meta: {
            title: 'Shop Home',
            description: 'Discover the latest products on our e-commerce platform.',
        },
    },
    {
        path: '/products/:slug',
        name: 'product-detail',
        component: () => import('../shop/views/ProductDetail.vue'),
        props: true, // Allows passing route params as props to the component
        meta: {
            title: 'Product Detail',
            description: 'View details of a specific product.',
        },
    },
    {
        path: '/cart',
        name: 'cart',
        component: () => import('../shop/views/Cart.vue'),
        meta: {
            title: 'Shopping Cart',
            description: 'Review your selected items before checkout.',
        },
    },
    {
        path: '/checkout',
        name: 'checkout',
        component: () => import('../shop/views/Checkout.vue'),
        meta: {
            title: 'Checkout',
            requiresAuth: true, // User must be logged in to checkout
            description: 'Complete your purchase securely.',
        },
    },
    {
        path: '/order-confirmation/:orderId',
        name: 'order-confirmation',
        component: () => import('../shop/views/OrderConfirmation.vue'),
        props: true,
        meta: {
            title: 'Order Confirmation',
            requiresAuth: true,
            description: 'Your order has been successfully placed.',
        },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../shop/views/Auth/Login.vue'),
        meta: {
            title: 'Login',
            guestOnly: true, // Only accessible to guests (not logged in users)
            description: 'Log in to your account.',
        },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../shop/views/Auth/Register.vue'),
        meta: {
            title: 'Register',
            guestOnly: true,
            description: 'Create a new account.',
        },
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('../shop/views/User/Profile.vue'),
        meta: {
            title: 'User Profile',
            requiresAuth: true,
            description: 'Manage your user profile and orders.',
        },
    },

    // Admin Panel Routes
    {
        path: '/admin',
        name: 'admin-dashboard',
        component: () => import('../admin/views/Dashboard.vue'),
        meta: {
            title: 'Admin Dashboard',
            requiresAuth: true,
            requiresAdmin: true, // Specific guard for admin roles
            description: 'Overview of the e-commerce platform administration.',
        },
    },
    {
        path: '/admin/products',
        name: 'admin-products',
        component: () => import('../admin/views/ProductManagement.vue'),
        meta: {
            title: 'Manage Products',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'Manage product listings, inventory, and categories.',
        },
    },
    {
        path: '/admin/products/create',
        name: 'admin-product-create',
        component: () => import('../admin/views/ProductForm.vue'),
        meta: {
            title: 'Create Product',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'Add a new product to the catalog.',
        },
    },
    {
        path: '/admin/products/:id/edit',
        name: 'admin-product-edit',
        component: () => import('../admin/views/ProductForm.vue'),
        props: true,
        meta: {
            title: 'Edit Product',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'Edit an existing product.',
        },
    },
    {
        path: '/admin/orders',
        name: 'admin-orders',
        component: () => import('../admin/views/OrderManagement.vue'),
        meta: {
            title: 'Manage Orders',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'View and manage customer orders.',
        },
    },
    {
        path: '/admin/orders/:id',
        name: 'admin-order-detail',
        component: () => import('../admin/views/OrderDetail.vue'),
        props: true,
        meta: {
            title: 'Order Detail',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'View details of a specific order.',
        },
    },
    {
        path: '/admin/analytics',
        name: 'admin-analytics',
        component: () => import('../admin/views/AnalyticsDashboard.vue'),
        meta: {
            title: 'Analytics Dashboard',
            requiresAuth: true,
            requiresAdmin: true,
            description: 'Access detailed analytics and reports (integrated with Microservices-based Analytics Dashboard).',
        },
    },
    // Cross-project context: Example of linking to an external blog or content
    {
        path: '/blog',
        name: 'blog',
        // This could either be an internal component fetching blog posts
        // or a redirect to an external blog powered by the Markdown Blog Generator CLI
        beforeEnter: (to, from, next) => {
            // Example: Redirect to an external blog URL
            // window.location.href = 'https://blog.your-ecommerce.com';
            // Or, if it's an internal route:
            next();
        },
        component: () => import('../shop/views/BlogListing.vue'), // Placeholder for internal blog
        meta: {
            title: 'Our Blog',
            description: 'Read our latest articles and news.',
        },
    },

    // Catch-all 404 route
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../shop/views/NotFound.vue'),
        meta: {
            title: 'Page Not Found',
            description: 'The page you are looking for does not exist.',
        },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL), // Use base URL from Vite config
    routes,
    scrollBehavior(to, from, savedPosition) {
        // Always scroll to top
        return { top: 0 };
    },
});

// Navigation Guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore(); // Get the auth store instance

    // Set page title
    document.title = to.meta.title ? `${to.meta.title} | E-commerce Platform` : 'E-commerce Platform';

    // Check if authentication is required
    if (to.meta.requiresAuth) {
        if (!authStore.isAuthenticated) {
            // If not authenticated, redirect to login page
            next({ name: 'login', query: { redirect: to.fullPath } });
            return;
        }

        // Check for admin role if required
        if (to.meta.requiresAdmin && !authStore.isAdmin) {
            // If not an admin, redirect to a forbidden page or home
            next({ name: 'home' }); // Or a dedicated 'forbidden' page
            return;
        }
    }

    // Redirect authenticated users from guest-only routes (login/register)
    if (to.meta.guestOnly && authStore.isAuthenticated) {
        next({ name: 'home' });
        return;
    }

    next(); // Continue to the route
});

export default router;