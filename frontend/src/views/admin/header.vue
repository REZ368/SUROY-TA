<template>
  <header class="bg-white text-black p-4 flex justify-between items-center shadow-md border shadow-black w-full md:w-[calc(100%-16rem)] ml-auto">
    <!-- Logo Image -->
    <img
              src="@/assets/suroy.png"
              alt="Logo"
              class="h-16 w-auto glowing-logo drop-shadow-[0_0_5px_black] filter"
              style="filter: drop-shadow(2px 2px 2px black);"
            />

    <!-- Dashboard Title -->
    <h1 class="text-2xl font-extrabold text-black flex-1">SUROY-TA!</h1>

    <!-- User Info Section -->
    <div class="user-info flex items-center space-x-6">
      <img
      src="@/assets/img/admin.jpg"
      alt="logo"
      class="h-12 w-auto rounded-full shadow-md shadow-black border"
    />
      <span class="text-md font-semibold text-black">{{ username }} || Admin</span> <!-- Display the logged-in user's name -->
      
      <!-- Logout Button -->
      <button
        @click="logout"
        class="flex items-center shadow-md shadow-black bg-green-500 text-white py-2 px-6 font-bold rounded-lg hover:bg-red-700 transition-colors duration-300"
      >
        <i class="fas fa-sign-out-alt"></i>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router'; // Import useRouter for navigation

const router = useRouter(); // Initialize router
const username = ref(''); // Reactive variable to hold the username

// On component mount, retrieve the username from local storage
onMounted(() => {
  username.value = localStorage.getItem('username') || 'Guest'; // Default to 'Guest' if not found
});

const logout = () => {
  // Clear the bearer token and user data from local storage
  localStorage.removeItem('token'); // Remove the token
  localStorage.removeItem('username'); // Remove the username
  localStorage.removeItem('profilePhoto'); // Remove the profile photo
  localStorage.removeItem('user_type'); // Remove the user type

  console.log('Logout clicked'); // Log the logout action

  // Redirect to the login page or home page
  router.push('/login'); // Redirect to the login page
  window.location.reload();
};
</script>

<style scoped>
/* Scoped styles are minimal since most styles are handled by Tailwind CSS */
</style>
