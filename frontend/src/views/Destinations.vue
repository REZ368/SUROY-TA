<template>
    <div class="max-w-lg mx-auto p-6 bg-white shadow-lg rounded-lg">
      <h2 class="text-2xl font-semibold text-center text-gray-800">Contact Us</h2>
      <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
        <div>
          <label for="name" class="block text-sm font-semibold text-gray-700">Name</label>
          <input
            v-model="name"
            type="text"
            id="name"
            placeholder="Your Name"
            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
            required
          />
        </div>
  
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
          <input
            v-model="email"
            type="email"
            id="email"
            placeholder="Your Email"
            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
            required
          />
        </div>
  
        <div>
          <label for="message" class="block text-sm font-semibold text-gray-700">Message</label>
          <textarea
            v-model="message"
            id="message"
            placeholder="Your Message"
            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
            rows="4"
            required
          ></textarea>
        </div>
  
        <div>
          <button
            type="submit"
            class="w-full py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500"
          >
            Send Message
          </button>
        </div>
      </form>
  
      <p v-if="successMessage" class="mt-4 text-center text-sm text-teal-600">{{ successMessage }}</p>
      <p v-if="errorMessage" class="mt-4 text-center text-sm text-red-600">{{ errorMessage }}</p>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import axios from 'axios';
  
  const name = ref('');
  const email = ref('');
  const message = ref('');
  const successMessage = ref('');
  const errorMessage = ref('');
  
  const handleSubmit = async () => {
    try {
      const response = await axios.post('http://localhost:3000/api/contact', {
        name: name.value,
        email: email.value,
        message: message.value,
      });
  
      successMessage.value = response.data.message;
      name.value = '';
      email.value = '';
      message.value = '';
      errorMessage.value = '';
    } catch (error) {
      errorMessage.value = error.response?.data.message || 'An error occurred. Please try again later.';
      successMessage.value = '';
    }
  };
  </script>
  
  <style scoped>
  /* You can add additional styles here if needed */
  </style>
  