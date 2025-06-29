<template>
  <div class="product-card" @click="viewProductDetail">
    <div class="product-card__image-container">
      <img :src="product.image_url" :alt="product.name" class="product-card__image" loading="lazy" />
    </div>
    <div class="product-card__details">
      <h3 class="product-card__name">{{ product.name }}</h3>
      <p class="product-card__price">{{ formattedPrice }}</p>
      <button @click.stop="addToCart" class="product-card__add-to-cart-btn">
        Add to Cart
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';

/**
 * Interface for a Product object.
 * This ensures type safety for the 'product' prop.
 */
interface Product {
  id: string;
  name: string;
  slug: string;
  description: string;
  price: number;
  image_url: string;
  // Additional properties that might be present but not strictly required for the card
  category_id?: string;
  stock?: number;
  currency?: string; // e.g., 'USD', 'EUR'
}

export default defineComponent({
  name: 'ProductCard',
  props: {
    /**
     * The product object to display in the card.
     * It's required and must conform to the Product interface.
     */
    product: {
      type: Object as PropType<Product>,
      required: true,
    },
  },
  setup(props) {
    const router = useRouter();
    const store = useStore();

    /**
     * Computed property to format the product price into a currency string.
     * Uses Intl.NumberFormat for robust internationalization.
     */
    const formattedPrice = computed(() => {
      // Default to USD if currency is not specified in product data
      const currency = props.product.currency || 'USD';
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
      }).format(props.product.price);
    });

    /**
     * Navigates to the product detail page when the product card is clicked.
     * Uses the product's slug for a clean URL.
     */
    const viewProductDetail = () => {
      router.push({ name: 'ProductDetail', params: { slug: props.product.slug } });
    };

    /**
     * Adds the current product to the shopping cart.
     * Dispatches an action to the Vuex store's 'cart' module.
     * Prevents event propagation to avoid triggering `viewProductDetail`.
     */
    const addToCart = async () => {
      try {
        // Dispatch an action to the cart module in the Vuex store
        // Assumes a 'cart' module exists with an 'addProductToCart' action
        await store.dispatch('cart/addProductToCart', {
          productId: props.product.id,
          quantity: 1, // Default quantity when adding from a product card
        });
        // In a real application, you might integrate with a global notification service
        // For example: store.dispatch('notifications/showSuccess', 'Product added to cart!');
        console.log(`Product "${props.product.name}" (ID: ${props.product.id}) added to cart.`);

        // --- Cross-Project Context: Analytics Integration ---
        // If an analytics microservice is available, we could send an event here.
        // Example:
        // const analyticsApiUrl = import.meta.env.VITE_ANALYTICS_SERVICE_URL;
        // if (analyticsApiUrl) {
        //   fetch(`${analyticsApiUrl}/api/v1/events`, {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({
        //       event_type: 'product_added_to_cart',
        //       user_id: store.getters['auth/userId'], // Assuming auth module exists
        //       product_id: props.product.id,
        //       quantity: 1,
        //       timestamp: new Date().toISOString(),
        //     }),
        //   }).catch(err => console.error('Failed to send analytics event:', err));
        // }
        // ---------------------------------------------------

      } catch (error) {
        console.error('Error adding product to cart:', error);
        // In a real application, show an error notification
        // For example: store.dispatch('notifications/showError', 'Failed to add product to cart.');
      }
    };

    return {
      formattedPrice,
      viewProductDetail,
      addToCart,
    };
  },
});
</script>

<style scoped lang="scss">
/* Import global variables or mixins if available */
@import '../../sass/variables'; // Assuming _variables.scss defines $primary-color, $accent-color, etc.

.product-card {
  background-color: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  height: 100%; /* Ensures cards in a grid have consistent height */
  text-decoration: none; /* Remove underline from links if card itself becomes a link */
  color: inherit; /* Inherit text color */

  &:hover {
    transform: translateY(-5px