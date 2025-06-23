<template>
    <Section>
      <div
        class="flex flex-col sm:flex-row w-full max-w-2xl mb-1 items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-10 p-2 rounded-full shadow-lg">
        <!-- Search Input -->
        <input
          v-model="searchQuery"
          @keyup.enter="navigateToResults"
          type="text"
          placeholder="Search for ex: Cebu, Naga, Carcar, Argao, etc.."
          class="flex-1 pl-20 pr-12 py-2 sm:py-3 text-sm sm:text-base rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-teal-500 border border-gray-100 shadow-md shadow-black"
        />
  
        <!-- Search Button -->
        <button
          @click="navigateToResults"
          class="bg-teal-600 text-white px-6 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-full shadow-md shadow-black hover:bg-teal-700 transition flex items-center justify-center space-x-2">
          <span class="material-symbols-outlined text-lg sm:text-xl">search</span>
          <span>Search</span>
        </button>
      </div>
    </Section>
  </template>
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const router = useRouter();
const searchQuery = ref('');

const navigateToResults = () => {
  if (!searchQuery.value.trim()) {
    Swal.fire('Oops!', 'Please enter a destination to search.', 'warning');
    return;
  }

  router.push({ name: 'Results', query: { destination: searchQuery.value.trim() } });
};
</script>
