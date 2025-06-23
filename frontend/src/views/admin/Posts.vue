<template>
  <div class="p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto bg-white animate-fade-in">
    <h2 class="text-2xl font-bold mb-4">Destinations Management</h2>

    <!-- Posts Table -->
    <table class="min-w-full border border-gray-300 rounded-lg shadow-md">
      <thead>
        <tr class="bg-slate-900 text-white text-left">
          <th class="border border-gray-300 px-4 py-2">ID</th>
          <th class="border border-gray-300 px-4 py-2">Title</th>
          <th class="border border-gray-300 px-4 py-2">Author</th>
          <th class="border border-gray-300 px-4 py-2">Category</th>
          <th class="border border-gray-300 px-4 py-2">Destination</th>
          <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="post in paginatedPosts" :key="post.post_id" class="hover:bg-gray-50 transition">
          <td class="border border-gray-300 px-4 py-2">{{ post.post_id }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ post.title }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ post.author }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ post.category_name }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ post.destination }}</td>
          <td class="border border-gray-300 px-4 py-2 flex justify-center space-x-2">
            <button
              @click="viewPost(post)"
              class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition flex items-center space-x-2"
            >
              <i class="fas fa-eye"></i>
              <span>View</span>
            </button>
            <button
              @click="togglePostVisibility(post.post_id, post.hidden)"
              :class="post.hidden ? 'bg-green-500' : 'bg-red-500'"
              :disabled="loading"
              class="text-white px-3 py-1 rounded hover:opacity-80 transition flex items-center space-x-2"
            >
              <i :class="post.hidden ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              <span>{{ post.hidden ? 'Unhide' : 'Hide' }}</span>
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 disabled:opacity-50"
      >
        Previous
      </button>
      <span class="font-semibold text-gray-700">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 disabled:opacity-50"
      >
        Next
      </button>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">Post Details</h3>
          <button @click="closeModal" class="text-red-500 hover:text-red-600 transition">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div>
          <p><strong>ID:</strong> {{ selectedPost.post_id }}</p>
          <p><strong>Title:</strong> {{ selectedPost.title }}</p>
          <p><strong>Author:</strong> {{ selectedPost.author }}</p>
          <p><strong>Category:</strong> {{ selectedPost.category_name }}</p>
          <p><strong>Destination:</strong> {{ selectedPost.destination }}</p>
          <p><strong>Content:</strong> {{ selectedPost.content }}</p>
        </div>
        <div class="mt-4">
          <button @click="closeModal" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const posts = ref([]);
const currentPage = ref(1);
const itemsPerPage = 5;
const showModal = ref(false);
const selectedPost = ref({});
const loading = ref(false);
const apiUrl = 'http://localhost:3000/api/posts'; // Define the base API URL

// Fetch posts
const fetchPosts = async () => {
  try {
    const response = await axios.get(apiUrl);
    const storedStatuses = JSON.parse(localStorage.getItem('hiddenStatuses') || '{}');
    
    posts.value = response.data.map(post => ({
      ...post,
      hidden: storedStatuses[post.post_id] ?? post.hidden, // Use localStorage status if available
    }));
  } catch (error) {
    console.error('Error fetching posts:', error);
  }
};

onMounted(fetchPosts);

// View Post
const viewPost = (post) => {
  selectedPost.value = post;
  showModal.value = true;
};

// Close Modal
const closeModal = () => {
  showModal.value = false;
};

// Save Hidden Status to LocalStorage
const saveHiddenStatus = (id, status) => {
  const hiddenStatuses = JSON.parse(localStorage.getItem('hiddenStatuses') || '{}');
  hiddenStatuses[id] = status;
  localStorage.setItem('hiddenStatuses', JSON.stringify(hiddenStatuses));
};

// Toggle Post Visibility
const togglePostVisibility = async (id, currentStatus) => {
  loading.value = true; // Set loading state to true
  try {
    const response = await axios.patch(`${apiUrl}/destinations/${id}/toggle-hide`, { hidden: !currentStatus });
    const post = posts.value.find(post => post.post_id === id);
    if (post) {
      post.hidden = response.data.hidden;
      saveHiddenStatus(id, response.data.hidden); // Save the updated status to localStorage
    }
  } catch (error) {
    console.error('Error toggling post visibility:', error);
  } finally {
    loading.value = false; // Reset loading state after the request
  }
};

// Pagination
const paginatedPosts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return posts.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => Math.ceil(posts.value.length / itemsPerPage));

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};
</script>
