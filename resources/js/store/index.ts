import { createStore } from 'vuex';
import axios from 'axios';

// Define interfaces for better type safety
interface Product {
  id: number;
  name: string;
  slug: string;
  description: string;
  price: number;
  image_url: string;
  category: string;
  stock: number;
  // Add other product properties as needed
}

interface CartItem {
  product: Product;
  quantity: number;
}

interface Order {
  id: number;
  user_id: number;
  status: string;
  total_amount: number;
  items: CartItem[]; // Assuming order items are similar to cart items
  created_at: string;
  // Add other order properties
}

interface User {
  id: number;
  name: string;
  email: string;
  role: 'customer' | 'admin';
  // Add other user properties
}

interface RootState {
  isLoading: boolean;
  error: string | null;
  user: User | null;
  isAuthenticated: boolean;
}

interface ProductsState {
  allProducts: Product[];
  selectedProduct: Product | null;
  categories: string[];
}

interface CartState {
  items: CartItem[];
}

interface OrdersState {
  userOrders: Order[];
  selectedOrder: Order | null;
}

interface AdminState {
  adminOrders: Order[];
  adminProducts: Product[];
  adminUsers: User[];
  analyticsData: any; // Flexible type for analytics data
}

// Root Store
const rootStore = {
  state: (): RootState => ({
    isLoading: false,
    error: null,
    user: null,
    isAuthenticated: false,
  }),
  getters: {
    isAuthenticated: (state: RootState) => state.isAuthenticated,
    currentUser: (state: RootState) => state.user,
    isAdmin: (state: RootState) => state.user?.role === 'admin',
    isLoading: (state: RootState) => state.isLoading,
    getError: (state: RootState) => state.error,
  },
  mutations: {
    SET_LOADING(state: RootState, status: boolean) {
      state.isLoading = status;
    },
    SET_ERROR(state: RootState, message: string | null) {
      state.error = message;
    },
    SET_USER(state: RootState, user: User | null) {
      state.user = user;
      state.isAuthenticated = !!user;
    },
    CLEAR_AUTH(state: RootState) {
      state.user = null;
      state.isAuthenticated = false;
      localStorage.removeItem('authToken'); // Clear token on logout
    },
  },
  actions: {
    async initializeAuth({ commit }) {
      const token = localStorage.getItem('authToken');
      if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        try {
          // Attempt to fetch user data to validate token
          const response = await axios.get('/api/user'); // Assuming a Laravel endpoint for authenticated user
          commit('SET_USER', response.data);
        } catch (error) {
          console.error('Failed to initialize auth:', error);
          commit('CLEAR_AUTH');
        }
      }
    },
    async login({ commit }, credentials) {
      commit('SET_LOADING', true);
      commit('SET_ERROR', null);
      try {
        const response = await axios.post('/api/login', credentials);
        const { user, token } = response.data;
        localStorage.setItem('authToken', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        commit('SET_USER', user);
        return true;
      } catch (error: any) {
        commit('SET_ERROR', error.response?.data?.message || 'Login failed.');
        return false;
      } finally {
        commit('SET_LOADING', false);
      }
    },
    async logout({ commit }) {
      commit('SET_LOADING', true);
      try {
        await axios.post('/api/logout'); // Invalidate token on backend
      } catch (error) {
        console.error('Logout failed on server:', error);
      } finally {
        commit('CLEAR_AUTH');
        delete axios.defaults.headers.common['Authorization'];
        commit('SET_LOADING', false);
      }
    },
  },
};

// Products Module
const productsModule = {
  namespaced: true,
  state: (): ProductsState => ({
    allProducts: [],
    selectedProduct: null,
    categories: [],
  }),
  getters: {
    featuredProducts: (state: ProductsState) => state.allProducts.filter(p => p.price > 50).slice(0, 5), // Example logic
    productById: (state: ProductsState) => (id: number) => state.allProducts.find(p => p.id === id),
    allCategories: (state: ProductsState) => state.categories,
  },
  mutations: {
    SET_PRODUCTS(state: ProductsState, products: Product[]) {
      state.allProducts = products;
    },
    SET_PRODUCT(state: ProductsState, product: Product | null) {
      state.selectedProduct = product;
    },
    SET_CATEGORIES(state: ProductsState, categories: string[]) {
      state.categories = categories;
    },
  },
  actions: {
    async fetchProducts({ commit, rootCommit }, params?: Record<string, any>) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const response = await axios.get('/api/v1/products', { params });
        commit('SET_PRODUCTS', response.data.data); // Assuming paginated data
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || 'Failed to fetch products.', { root: true });
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
    async fetchProductById({ commit, rootCommit }, id: number) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const response = await axios.get(`/api/v1/products/${id}`);
        commit('SET_PRODUCT', response.data.data);
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || `Failed to fetch product with ID ${id}.`, { root: true });
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
    async fetchCategories({ commit, rootCommit }) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const response = await axios.get('/api/v1/categories'); // Assuming a categories endpoint
        commit('SET_CATEGORIES', response.data.data);
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || 'Failed to fetch categories.', { root: true });
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
    // Action to interact with AI-Powered Document Categorizer & Search (backend service)
    async smartSearchProducts({ commit, rootCommit }, query: string) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        // This would hit a backend endpoint that then communicates with the AI service
        const response = await axios.get(`/api/v1/products/smart-search?q=${query}`);
        commit('SET_PRODUCTS', response.data.data);
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || 'Smart search failed.', { root: true });
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
  },
};

// Cart Module
const cartModule = {
  namespaced: true,
  state: (): CartState => ({
    items: JSON.parse(localStorage.getItem('cartItems') || '[]'), // Persist cart in local storage
  }),
  getters: {
    cartTotal: (state: CartState) => state.items.reduce((total, item) => total + item.product.price * item.quantity, 0),
    cartItemCount: (state: CartState) => state.items.reduce((count, item) => count + item.quantity, 0),
    cartItems: (state: CartState) => state.items,
  },
  mutations: {
    ADD_ITEM_TO_CART(state: CartState, { product, quantity = 1 }: { product: Product; quantity?: number }) {
      const existingItem = state.items.find(item => item.product.id === product.id);
      if (existingItem) {
        existingItem.quantity += quantity;
      } else {
        state.items.push({ product, quantity });
      }
      localStorage.setItem('cartItems', JSON.stringify(state.items));
    },
    UPDATE_ITEM_QUANTITY(state: CartState, { productId, quantity }: { productId: number; quantity: number }) {
      const item = state.items.find(item => item.product.id === productId);
      if (item) {
        item.quantity = quantity;
        if (item.quantity <= 0) {
          state.items = state.items.filter(i => i.product.id !== productId);
        }
      }
      localStorage.setItem('cartItems', JSON.stringify(state.items));
    },
    REMOVE_ITEM_FROM_CART(state: CartState, productId: number) {
      state.items = state.items.filter(item => item.product.id !== productId);
      localStorage.setItem('cartItems', JSON.stringify(state.items));
    },
    CLEAR_CART(state: CartState) {
      state.items = [];
      localStorage.removeItem('cartItems');
    },
  },
  actions: {
    addToCart({ commit }, payload: { product: Product; quantity?: number }) {
      commit('ADD_ITEM_TO_CART', payload);
    },
    updateCartItem({ commit }, payload: { productId: number; quantity: number }) {
      commit('UPDATE_ITEM_QUANTITY', payload);
    },
    removeFromCart({ commit }, productId: number) {
      commit('REMOVE_ITEM_FROM_CART', productId);
    },
    async checkout({ commit, getters, rootCommit }) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const cartItems = getters.cartItems.map((item: CartItem) => ({
          product_id: item.product.id,
          quantity: item.quantity,
          price: item.product.price,
        }));

        const response = await axios.post('/api/v1/checkout', { items: cartItems });
        commit('CLEAR_CART');
        // Optionally, dispatch an action to fetch user orders after successful checkout
        // rootCommit('orders/fetchUserOrders', null, { root: true });
        return response.data; // Contains order confirmation, payment status etc.
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || 'Checkout failed. Please try again.', { root: true });
        throw error; // Re-throw to allow component to catch
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
  },
};

// Orders Module (for customer's orders)
const ordersModule = {
  namespaced: true,
  state: (): OrdersState => ({
    userOrders: [],
    selectedOrder: null,
  }),
  getters: {
    recentOrders: (state: OrdersState) => state.userOrders.slice(0, 5),
    orderById: (state: OrdersState) => (id: number) => state.userOrders.find(order => order.id === id),
  },
  mutations: {
    SET_USER_ORDERS(state: OrdersState, orders: Order[]) {
      state.userOrders = orders;
    },
    SET_SELECTED_ORDER(state: OrdersState, order: Order | null) {
      state.selectedOrder = order;
    },
  },
  actions: {
    async fetchUserOrders({ commit, rootCommit }) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const response = await axios.get('/api/v1/orders'); // User-specific orders
        commit('SET_USER_ORDERS', response.data.data);
      } catch (error: any) {
        rootCommit('SET_ERROR', error.response?.data?.message || 'Failed to fetch your orders.', { root: true });
      } finally {
        rootCommit('SET_LOADING', false, { root: true });
      }
    },
    async fetchOrderById({ commit, rootCommit }, id: number) {
      rootCommit('SET_LOADING', true, { root: true });
      rootCommit('SET_ERROR', null, { root: true });
      try {
        const response = await axios.get(`/api/v1/orders/${id}`);
        commit('SET_SELECTED_ORDER', response.data.data);
      } catch (error: any)