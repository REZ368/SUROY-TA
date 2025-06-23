<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2'; // Import SweetAlert

// Reactive state variables
const promotions = ref([]);
const currentPage = ref(1);
const totalPages = ref(1); // Default to 1 page
const itemsPerPage = 5;
const modalImages = ref([]);
const modalPromotion = ref(null);

// Base URL for images
const baseUrl = 'http://localhost:3000';

// Fetch promotions from the API
const fetchPromotions = async () => {
  try {
    const response = await axios.get('http://localhost:3000/api/promotions/getallpromotions', {
      params: { page: currentPage.value, limit: itemsPerPage },
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    });

    promotions.value = response.data.data || [];
    totalPages.value = response.data.totalPages || 1; // Fallback to 1 if undefined
  } catch (error) {
    console.error('Error fetching promotions:', error);
    Swal.fire('Error', 'Failed to load promotions.', 'error');
  }
};

// Change page with validation
const changePage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page;
    fetchPromotions();
  }
};

// Computed property for paginated promotions
const paginatedPromotions = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  return promotions.value.slice(startIndex, startIndex + itemsPerPage);
});

// Update promotion status
const updateStatus = async (promotion) => {
  if (!promotion.newStatus || !['approved', 'pending', 'denied'].includes(promotion.newStatus)) {
    Swal.fire('Error', 'Please select a valid status.', 'error');
    return;
  }

  try {
    await axios.put(
      `http://localhost:3000/api/promotions/updatepromotionstatus/${promotion.id}`,
      { status: promotion.newStatus },
      { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
    );
    Swal.fire('Success', 'Promotion status updated successfully.', 'success');
    fetchPromotions();
  } catch (error) {
    console.error('Error updating promotion status:', error);
    Swal.fire('Error', 'Failed to update promotion status.', 'error');
  }
};

// Open promotion details modal
const viewPromotionDetails = (promotion) => {
  modalPromotion.value = promotion;
  modalImages.value = [
    baseUrl + promotion.businessCertificateImage,
    ...promotion.images.map((img) => baseUrl + img),
  ];
};

// Close modal
const closeModal = () => {
  modalImages.value = [];
  modalPromotion.value = null;
};

// Fetch data when the component mounts
onMounted(fetchPromotions);
</script>

<template>
  <div class="p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto animate-fade-in">
    <h1 class="text-2xl font-bold mb-6">Promotions Management</h1>

    <!-- Promotions Table -->
    <table class="min-w-full table-auto bg-white border border-gray-300 shadow-md rounded-lg mb-6">
      <thead class="bg-slate-900 text-white">
        <tr>
          <th class="px-4 py-2 text-left">Owner</th>
          <th class="px-4 py-2 text-left">Title</th>
          <th class="px-4 py-2 text-left">Description</th>
          <th class="px-4 py-2 text-left">Start Date</th>
          <th class="px-4 py-2 text-left">End Date</th>
          <th class="px-4 py-2 text-left">Destination</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Edit Status</th>
          <th class="px-4 py-2 text-left">Update Status</th>
          <th class="px-4 py-2 text-left">View Details</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="promotion in paginatedPromotions" :key="promotion.id">
          <td class="px-4 py-2 border-b">{{ promotion.author.username }}</td>
          <td class="px-4 py-2 border-b">{{ promotion.title }}</td>
          <td class="px-4 py-2 border-b">{{ promotion.description.slice(0, 100) }}...</td>
          <td class="px-4 py-2 border-b">{{ new Date(promotion.start_date).toLocaleDateString() }}</td>
          <td class="px-4 py-2 border-b">{{ new Date(promotion.end_date).toLocaleDateString() }}</td>
          <td class="px-4 py-2 border-b">{{ promotion.destination }}</td>
          <td class="px-4 py-2 border-b font-semibold"
              :class="{
                'text-green-500': promotion.status === 'approved',
                'text-blue-500': promotion.status === 'pending',
                'text-red-500': promotion.status === 'denied'
              }">
            {{ promotion.status }}
          </td>
          <td class="px-4 py-2 border-b">
            <select v-model="promotion.newStatus" class="border border-gray-300 p-2">
              <option value="approved">Approve</option>
              <option value="pending">Pending</option>
              <option value="denied">Deny</option>
            </select>
          </td>
          <td>
            <button @click="updateStatus(promotion)"
              class="ml-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
              Update
            </button>
          </td>
          <td>
            <button @click="viewPromotionDetails(promotion)"
              class="ml-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700">
              View
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center space-x-4">
      <button
        @click="changePage(currentPage.value - 1)"
        :disabled="currentPage.value === 1"
        class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-teal-700 transition disabled:opacity-50">
        Previous
      </button>

      <button
        v-for="page in totalPages"
        :key="page"
        @click="changePage(page)"
        :class="{
          'bg-teal-500 text-white': currentPage.value === page,
          'bg-gray-200': currentPage.value !== page,
        }"
        class="px-4 py-2 rounded-lg">
        {{ page }}
      </button>

      <button
        @click="changePage(currentPage.value + 1)"
        :disabled="currentPage.value === totalPages.value"
        class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-teal-700 transition disabled:opacity-50">
        Next
      </button>
    </div>
  </div>

  <!-- Promotion Details Modal -->
  <div v-if="modalPromotion" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg max-w-lg">
      <h2 class="text-2xl font-semibold">{{ modalPromotion.title }}</h2>
      <p class="mt-2">{{ modalPromotion.description }}</p>
      <p class="mt-4">Destination: {{ modalPromotion.destination }}</p>
      <p>Start Date: {{ new Date(modalPromotion.start_date).toLocaleDateString() }}</p>
      <p>End Date: {{ new Date(modalPromotion.end_date).toLocaleDateString() }}</p>
      <p>Status: {{ modalPromotion.status }}</p>
      <p class="mb-6">Owner: {{ modalPromotion.author.username }}</p>

      <!-- Display Business Certificate Image -->
      <p class="font-bold">Business Certificate:</p>
      <img :src="baseUrl + modalPromotion.businessCertificateImage" alt="Business Certificate"
        class="mt-4 w-full rounded cursor-pointer" />

      <!-- Display Additional Images -->
      <div v-if="modalImages.length">
        <h3 class="mt-4 font-semibold">Additional Images:</h3>
        <div v-for="image in modalImages" :key="image" class="inline-block mt-2 mr-2">
          <img :src="image" alt="Additional Image" class="w-32 h-32 object-cover cursor-pointer"
            @click="image && window.open(image, '_blank')" />
        </div>
      </div>

      <button @click="closeModal" class="mt-4 text-white bg-red-500 p-2 rounded">Close</button>
    </div>
  </div>
</template>
