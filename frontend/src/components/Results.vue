<template>
    <section class="min-h-screen py-10 mt-20 animate-fade-in">
      <div class="mt-20 max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
        <template v-if="!isLoading">
            <h2 class="text-white font-sans text-md font-bold mb-4">Results : </h2>
          <!-- Posts Section -->
          <div v-if="searchResults.posts.length" class="col-span-full">
            <h2 class="text-white font-serif text-2xl font-bold mb-4">Destinations</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div
                v-for="post in searchResults.posts"
                :key="post.post_id"
                @click="goToPostDetail(post.post_id)"
                class="cursor-pointer bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105"
              >
                <img
                  v-if="post.image_url"
                  :src="post.image_url.split(',').map(url => `http://localhost:3000${url.trim()}`)[0]"
                  alt="Post Image"
                  class="w-full h-40 object-cover rounded-md mb-4"
                />
                <h3 class="text-teal-700 text-lg font-semibold">{{ post.title }}</h3>
                <p class="text-gray-600 text-sm mb-2 line-clamp-1">{{ post.content }}</p>
                <p class="text-green-600 text-sm font-bold mb-2">{{ post.destination }}</p>
                <div class="flex justify-between items-center text-gray-500 text-xs">
                  <span>Likes: {{ post.likes }}</span>
                  <span>Views: {{ post.views }}</span>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Promotions Section -->
          <div v-if="searchResults.promotions.length" class="col-span-full">
            <h2 class="text-white text-2xl font-bold mb-4 font-serif">Promotions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="promo in searchResults.promotions"
                :key="promo.id"
                @click="goToPromotionDetail(promo.id)"
                class="cursor-pointer bg-white p-4 rounded-lg shadow-lg hover:shadow-xl transition transform hover:scale-105"
              >
                <img
                  v-if="promo.image_url"
                  :src="promo.image_url.split(',').map(url => `http://localhost:3000${url.trim()}`)[0]"
                  alt="Promotion Image"
                  class="w-full h-40 object-cover rounded-md mb-4"
                />
                <h3 class="text-teal-700 text-lg font-semibold">{{ promo.title }}</h3>
                <p class="text-gray-600 text-sm mb-2 line-clamp-1">{{ promo.description }}</p>
                <p class="text-green-600 text-sm font-semibold mb-2">{{ promo.destination }}</p>
                <span class="text-xs text-gray-500">Valid until: {{ promo.end_date }}</span>
              </div>
            </div>
          </div>
        </template>
  
        <!-- No Results Found -->
        <p
          v-if="!isLoading && !searchResults.posts.length && !searchResults.promotions.length"
          class="text-gray-500 text-center col-span-full"
        >
          No results found for "{{ destinationQuery }}".
        </p>
      </div>
  
      <!-- Loading Spinner -->
      <div v-if="isLoading" class="flex justify-center items-center mt-20">
        <span class="loader"></span>
      </div>
    </section>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  const route = useRoute();
  const router = useRouter();
  const destinationQuery = ref(route.query.destination || '');
  const searchResults = ref({ posts: [], promotions: [] });
  const isLoading = ref(false);
  
  // Fetch search results from the backend
  const fetchSearchResults = async () => {
    if (!destinationQuery.value.trim()) {
      Swal.fire('Oops!', 'No destination specified.', 'error');
      return;
    }
  
    isLoading.value = true;
    try {
      const response = await axios.get('http://localhost:3000/api/search/searcha', {
        params: { destination: destinationQuery.value.trim() },
      });
      searchResults.value = response.data.data;
  
      if (!searchResults.value.posts.length && !searchResults.value.promotions.length) {
        Swal.fire('No Results', 'No posts or promotions found.', 'info');
      }
    } catch (error) {
      console.error('Error fetching results:', error);
      Swal.fire('Error', 'Failed to fetch search results.', 'error');
    } finally {
      isLoading.value = false;
    }
  };
  
  // Navigate to post detail page
  const goToPostDetail = (postId) => {
    router.push({ name: 'post-detail', params: { id: postId } });
  };
  
  // Navigate to promotion detail page
  const goToPromotionDetail = (promoId) => {
    router.push({ name: 'promotionlists', params: { id: promoId } });
  };
  
  onMounted(() => {
    fetchSearchResults();
  });
  </script>
  
  <style>
  .loader {
    border: 4px solid transparent;
    border-top: 4px solid teal;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% {
      transform: rotate(0);
    }
    100% {
      transform: rotate(360deg);
    }
  }
  </style>
  