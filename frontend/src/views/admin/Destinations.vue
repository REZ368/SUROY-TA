<template>
  <div class="p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto animate-fade-in">
    <h2 class="text-2xl font-bold mb-4">Destinations Management</h2>

    <!-- Search Input -->
    <div class="mb-4">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search by destination"
        class="border border-gray-300 rounded px-4 py-2 w-full focus:ring focus:ring-blue-200"
      />
    </div>

    <table class="min-w-full border border-gray-300 rounded-lg shadow-md">
      <thead>
        <tr class="bg-slate-800 text-white text-left">
          <th class="border border-gray-300 px-4 py-2">ID</th>
          <th class="border border-gray-300 px-4 py-2">Destination</th>
          <th class="border border-gray-300 px-4 py-2">Status</th>
          <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="destination in paginatedDestinations"
          :key="destination.id"
          class="hover:bg-gray-100 transition"
        >
          <td class="border border-gray-300 px-4 py-2">{{ destination.id }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ destination.destination }}</td>
          <td class="border border-gray-300 px-4 py-2">
            <span
              :class="destination.hidden ? 'text-red-500' : 'text-green-500'"
              class="font-semibold"
            >
              {{ destination.hidden ? 'Hidden' : 'Visible' }}
            </span>
          </td>
          <td class="border border-gray-300 px-4 py-2 space-x-4 flex">
            <button
              @click="viewPost(destination.id)"
              class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition"
            >
              View
            </button>
            <button
              @click="toggleHide(destination.id, destination.hidden)"
              :class="destination.hidden ? 'bg-green-500' : 'bg-red-500'"
              class="text-white px-3 py-1 rounded hover:opacity-80 transition"
            >
              {{ destination.hidden ? 'Unhide' : 'Hide' }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
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
  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const destinations = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;

// Fetch destinations
const fetchDestinations = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get('http://localhost:3000/api/destinations', {
      headers: { Authorization: token },
    });
    destinations.value = response.data.map(destination => ({
      id: destination.id,
      destination: destination.destination,
      hidden: destination.hidden,
    }));
  } catch (error) {
    console.error('Error fetching destinations:', error);
  }
};

// View post handler
const viewPost = (id) => {
  alert(`Viewing post with ID: ${id}`); // Replace with navigation logic
};

// Toggle hide/unhide
const toggleHide = async (id, currentStatus) => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.patch(`http://localhost:3000/api/destinations/${id}/toggle-hide`, null, {
      headers: { Authorization: token },
    });
    const updatedStatus = response.data.hidden;

    const destination = destinations.value.find(dest => dest.id === id);
    if (destination) destination.hidden = updatedStatus;
  } catch (error) {
    console.error('Error toggling visibility:', error);
  }
};

onMounted(fetchDestinations);

// Filtered and paginated destinations
const filteredDestinations = computed(() => {
  return destinations.value.filter(destination =>
    destination.destination.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});
const paginatedDestinations = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredDestinations.value.slice(start, start + itemsPerPage);
});
const totalPages = computed(() => Math.ceil(filteredDestinations.value.length / itemsPerPage));

// Pagination handlers
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};
</script>
