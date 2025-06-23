<template>
  <section class="">
<div class="container mx-auto mt-6 p-4 sm:p-6">
 <ProfileHeader />
  <!-- Main Content Layout -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
    <!-- Profile Photo Section -->
    <div class="bg-white rounded-lg shadow-md shadow-black p-6 flex flex-col items-center ">
      <ProfilePhoto />
    </div>

    <!-- Edit Information Section -->
    <div class="col-span-2 bg-white rounded-lg shadow-md shadow-black p-6">
      <h2 class="text-2xl font-extrabold text-teal-700 mb-6">
        Edit Information
      </h2>
      <img
        :src="fullProfilePhotoUrl"
        alt="Profile Photo"
        class="h-24 w-24 rounded-full border border-teal-600 object-cover mt-4"
        @error="setDefaultProfileImage"
      />
      <label
        for="profilePhoto"
        class="block text-sm font-semibold text-teal-700 mt-6"
      >
        Profile Photo
      </label>
      <input
        type="file"
        id="profilePhoto"
        @change="handleFileUpload"
        class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 p-2"
      />
      <form @submit.prevent="updateProfile" class="space-y-4">
        <div>
          <label
            for="username"
            class="block text-sm font-medium text-teal-700"
          >
            Username
          </label>
          <input
            v-model="user.username"
            type="text"
            id="username"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 p-2"
          />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-teal-700">
            Email
          </label>
          <input
            v-model="user.email"
            type="email"
            id="email"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 p-2"
          />
        </div>
        <div>
          <label
            for="password"
            class="block text-sm font-medium text-teal-700"
          >
            Password
          </label>
          <input
            v-model="user.password"
            type="password"
            id="password"
            placeholder="Leave blank to keep current password"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 p-2"
          />
        </div>
        <button
          type="submit"
          class="w-full bg-teal-600 text-white py-2 px-4 rounded-lg hover:bg-teal-500 transition duration-300"
        >
          Update Profile
        </button>
      </form>
    </div>
  </div>
</div>
</section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import ProfilePhoto from '../components/ProfilePhoto.vue';
import ProfileHeader from '@/components/ProfileHeader.vue';

const user = ref({
  username: '',
  email: '',
  password: '',
  profilePhoto: '',
});

const userPosts = ref([]);
const baseUrl = 'http://localhost:3000'; // Base URL for API
const isEditModalOpen = ref(false);
const editPostData = ref({
  title: '',
  content: '',
  category_name: '',
  image_urls: [],
  post_id: null,
});

// Handle file upload for profile photo
const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    user.value.profilePhoto = file; // Store the file for upload
  }
};

// Computed property for profile photo URL
const fullProfilePhotoUrl = computed(() => {
  if (user.value.profilePhoto instanceof File || user.value.profilePhoto instanceof Blob) {
    // Create an object URL if the profilePhoto is a File or Blob
    return URL.createObjectURL(user.value.profilePhoto);
  } else if (user.value.profilePhoto) {
    // Use the base URL with the provided profile photo
    return `${baseUrl}${user.value.profilePhoto}`;
  } else {
    // Use a default fake profile image
    return 'https://p.kindpng.com/picc/s/207-2074624_white-gray-circle-avatar-png-transparent-png.png'; // Ensure this path points to a valid image in your public assets
  }
});


// Fetch user profile and posts
onMounted(async () => {
  try {
    const response = await axios.get(`${baseUrl}/api/profile`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    user.value = response.data.user; // Populate user data
    userPosts.value = response.data.posts.map((post) => {
      const imageUrls = post.image_url
        ? post.image_url.split(',').map((url) => `${baseUrl}${url.trim()}`)
        : []; // Split and prefix URLs
      return {
        ...post,
        currentImageUrl: imageUrls[0] || '/default-post-image.png', // Default image if none provided
        imageUrls, // Store all image URLs for future use if needed
      };
    });
  } catch (error) {
    console.error('Error fetching user profile:', error);
    Swal.fire('Error', 'Failed to load user profile.', 'error');
  }
});

// Update user profile
const updateProfile = async () => {
  const formData = new FormData();
  
  // Append only the fields that have values
  if (user.value.username) {
    formData.append('username', user.value.username);
  }
  if (user.value.email) {
    formData.append('email', user.value.email);
  }
  if (user.value.password) {
    formData.append('password', user.value.password); // Append the password if provided
  }
  if (user.value.profilePhoto) {
    formData.append('profilePhoto', user.value.profilePhoto); // Append the file if provided
  }

  try {
    await axios.put(`${baseUrl}/api/profile`, formData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'multipart/form-data',
      },
    });
    Swal.fire('Success', 'Profile updated successfully!', 'success');
    window.location.reload();
  } catch (error) {
    console.error('Error updating profile:', error);
    Swal.fire('Error', 'Failed to update profile.', 'error');
  }
};

// Open the edit modal and populate the data
const openEditModal = (post) => {
  editPostData.value = {
    title: post.title,
    content: post.content,
    category_name: post.category_name,
    image_urls: post.imageUrls,
    post_id: post.post_id,
  };
  isEditModalOpen.value = true;
};

// Close the edit modal
const closeEditModal = () => {
  isEditModalOpen.value = false;
  editPostData.value = {
    title: '',
    content: '',
    category_name: '',
    image_urls: [],
    post_id: null,
  };
};

// Handle image uploads for posts
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files);
  editPostData.value.image_urls = files.map(file => URL.createObjectURL(file)); // Store the image URLs for preview
};

// Submit the edited post
const submitEditPost = async () => {
  try {
    const formData = new FormData();
    formData.append('title', editPostData.value.title);
    formData.append('content', editPostData.value.content);
    formData.append('category_name', editPostData.value.category_name);
    editPostData.value.image_urls.forEach((image) => {
      formData.append('images', image); // Append each image file
    });

    await axios.put(`${baseUrl}/api/posts/${editPostData.value.post_id}`, formData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    Swal.fire('Success', 'Post updated successfully!', 'success');
    closeEditModal(); // Close the modal after successful update
  } catch (error) {
    console.error('Error updating post:', error);
    Swal.fire('Error', 'Failed to update post.', 'error');
  }
};

// Delete post
const deletePost = async (postId) => {
  try {
    const response = await axios.delete(`${baseUrl}/api/posts/${postId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (response.data.message === 'Post deleted successfully') {
      userPosts.value = userPosts.value.filter((post) => post.post_id !== postId);
      Swal.fire('Deleted', 'Post deleted successfully!', 'success');
    } else {
      Swal.fire('Error', 'Failed to delete post.', 'error');
    }
  } catch (error) {
    console.error('Error deleting post:', error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to delete post.', 'error');
  }
};
</script>

<style>
body {
  background-color: #f0f0f0; /* Light gray background */
}
</style>