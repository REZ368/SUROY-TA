<template>
  <div class="p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto bg-white shadow-md shadow-black animate-fade-in">
    <h2 class="text-2xl font-bold mb-4">User Management</h2>

    <!-- Search Input -->
    <div class="mb-4">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search by name or email"
        class="border border-gray-300 rounded px-4 py-2 w-full"
      />
    </div>

    <table class="min-w-full border-collapse border border-gray-300 bg-white">
      <thead>
        <tr class="bg-slate-900 text-white">
          <th class="border border-gray-300 px-4 py-2">ID</th>
          <th class="border border-gray-300 px-4 py-2">Profile Photo</th>
          <th class="border border-gray-300 px-4 py-2">Name</th>
          <th class="border border-gray-300 px-4 py-2">Email</th>
          <th class="border border-gray-300 px-4 py-2">User_Type</th>
          <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in paginatedUsers" :key="user.user_id" class="hover:bg-gray-50 font-semibold">
          <td class="border border-gray-300 px-4 py-2">{{ user.user_id }}</td>
          <td class="border border-gray-300 px-4 py-2">
            <img :src="`http://localhost:3000${user.profile_photo}`" alt="Profile Photo" class="w-12 h-12 rounded-full object-cover" />
          </td>
          <td class="border border-gray-300 px-4 py-2">{{ user.username }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ user.email }}</td>
          <td class="border border-gray-300 px-4 py-2"><span class="p-2 rounded-full text-black">{{ user.user_type }}</span></td>
          <td class="border border-gray-300 px-4 py-2 space-x-2">
            <button @click="restrictUser(user.user_id)" v-if="!user.is_restricted" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition">
              <i class="fas fa-ban"></i> Restrict
            </button>
            <button @click="unrestrictUser(user.user_id)" v-if="user.is_restricted" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">
              <i class="fas fa-check"></i> Unrestrict
            </button>
            <button @click="viewUserDetails(user)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition">
              <i class="fas fa-eye"></i> View
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <div class="mt-4 flex justify-between items-center">
      <button @click="prevPage" :disabled="currentPage === 1" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
        Next
      </button>
    </div>
  </div>

  <!-- Modal for User Details -->
  <div v-if="selectedUser" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-lg w-full">
      <h2 class="text-xl font-bold mb-4">User Details</h2>
      <div class="mb-4">
        <img :src="`http://localhost:3000${selectedUser.profile_photo}`" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover mb-4"/>
        <p><strong>Username:</strong> {{ selectedUser.username }}</p>
        <p><strong>Email:</strong> {{ selectedUser.email }}</p>
        <p><strong>Bio:</strong> {{ selectedUser.bio || 'No bio available' }}</p>
        <p><strong>User Type:</strong> {{ selectedUser.user_type }}</p>
      </div>
      <button @click="closeModal" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Close</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const users = ref([]);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 5;
const selectedUser = ref(null);

// Fetch users from the backend
const fetchUsers = async () => {
  try {
    const response = await axios.get('http://localhost:3000/api/admin/users');
    users.value = response.data;
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

onMounted(fetchUsers);

// Methods to restrict and unrestrict users
const restrictUser = async (userId) => {
  try {
    const response = await axios.patch(`http://localhost:3000/api/admin/users/${userId}/restrict`);
    const updatedUser = response.data;
    const index = users.value.findIndex(user => user.user_id === updatedUser.user_id);
    if (index !== -1) {
      users.value[index] = updatedUser;
    }
    Swal.fire('Success', 'User has been restricted.', 'success');
  } catch (error) {
    console.error('Error restricting user:', error);
    Swal.fire('Error', 'Failed to restrict the user.', 'error');
  }
};

const unrestrictUser = async (userId) => {
  try {
    const response = await axios.patch(`http://localhost:3000/api/admin/users/${userId}/unrestrict`);
    const updatedUser = response.data;
    const index = users.value.findIndex(user => user.user_id === updatedUser.user_id);
    if (index !== -1) {
      users.value[index] = updatedUser;
    }
    Swal.fire('Success', 'User has been unrestricted.', 'success');
  } catch (error) {
    console.error('Error unrestricting user:', error);
    Swal.fire('Error', 'Failed to unrestrict the user.', 'error');
  }
};

// View user details in a modal
const viewUserDetails = (user) => {
  selectedUser.value = user;
};

// Close the modal
const closeModal = () => {
  selectedUser.value = null;
};

// Computed properties for filtering, pagination, etc.
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const userName = user.username ? user.username.toLowerCase() : '';
    const userEmail = user.email ? user.email.toLowerCase() : '';
    return (
      userName.includes(searchQuery.value.toLowerCase()) ||
      userEmail.includes(searchQuery.value.toLowerCase())
    );
  });
});

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage);
});

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};
</script>

<style scoped>
/* Modal Style */
.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>
