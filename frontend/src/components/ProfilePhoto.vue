<template>
  <div class="relative">
    <!-- Cover Photo -->
    <img
      src="@/assets/img/bitch.jpg" 
      alt="Cover Photo"
      class="w-full h-48 object-cover rounded-lg"
    />
    <div class="absolute top-32 left-4">
      <!-- Profile Photo -->
      <div class="relative">
        <img
          :src="fullProfilePhotoUrl"
          alt="Profile Photo"
          class="h-32 w-32 rounded-full border-4 border-white object-cover shadow-md"
          @error="setDefaultProfileImage"
        />
        <!-- Online Status Circle -->
        <div
          class="absolute bottom-0 right-0 bg-green-500 border-2 border-white w-6 h-6 rounded-full ring-2 ring-white"
        ></div>
      </div>
    </div>
    <div class="mt-16 ml-4">
      <h2 class="text-3xl font-bold text-gray-800 mb-1"><span>@</span>{{ userProfile.username }}</h2>
      <p class="ml-1 mb-3 font-light"><i class="fa-regular fa-envelope mr-2"></i>{{userProfile.email}}</p>
      <p class="text-gray-600 font-semibold">
       ( {{ userProfile.bio || 'This user has not set a bio yet.' }} )
      </p>
    </div>

    <!-- Bio Update Form -->
    <div class="mt-4 ml-4">
      <h3 class="text-xl font-semibold text-gray-700">Update Bio</h3>
      <form @submit.prevent="updateBio">
        <textarea
          v-model="editableBio"
          class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500 p-2"
          rows="3"
        ></textarea>
        <button
          type="submit"
          class="mt-2 bg-teal-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300"
        >
          Save Bio
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const userProfile = ref({
  username: '',
  bio: '',
  profile_photo: ''
});
const baseUrl = 'http://localhost:3000'; // Base URL for API

const editableBio = ref('');

// Computed property for profile photo URL
const fullProfilePhotoUrl = computed(() => {
  return userProfile.value.profile_photo
    ? `${baseUrl}${userProfile.value.profile_photo}`
    : 'https://cdn-icons-png.flaticon.com/512/12225/12225935.png'; // Default profile photo
});

// Fetch user profile and followers on component mount
onMounted(async () => {
  try {
    const response = await axios.get(`${baseUrl}/api/profile/myprofile`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    userProfile.value = response.data.user; // Populate user profile data
    editableBio.value = userProfile.value.bio || ''; // Set the editableBio to the user's current bio
  } catch (error) {
    console.error('Error fetching user profile or followers:', error);
  }
});

// Update bio with SweetAlert
const updateBio = async () => {
  try {
    await axios.put(`${baseUrl}/api/profile/bio`, { bio: editableBio.value }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    userProfile.value.bio = editableBio.value;
    Swal.fire({
      icon: 'success',
      title: 'Bio Updated',
      text: 'Your bio has been successfully updated.',
    });
  } catch (error) {
    console.error('Error updating bio:', error);
    Swal.fire({
      icon: 'error',
      title: 'Update Failed',
      text: 'There was an issue updating your bio. Please try again later.',
    });
  }
};

const setDefaultProfileImage = (event) => {
  event.target.src = 'https://cdn-icons-png.flaticon.com/512/12225/12225935.png'; // Default image URL
};
</script>

<style scoped>
/* Add any additional styles here */
</style>
