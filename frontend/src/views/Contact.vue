<template>
  <section>
  <div class="contact bg-slate-600 min-h-screen flex flex-col animate-fade-in ">
    <div class="flex flex-col flex-grow mt-16">
      <!-- Hero Section -->
      <div class="relative h-[300px] md:h-[400px]">
        <div
          class="absolute inset-0 bg-cover bg-center"
          :style="{ backgroundImage: 'url(/placeholder.svg?height=400&width=1200)' }"
        >
          <div class="absolute inset-0 bg-black/50"></div>
        </div>
        <div class="relative flex flex-col p-6 md:p-10 items-center justify-center h-full text-white">
          <h2 class="text-4xl font-extrabold text-center text-slate-100 mb-6">CONTACT US <i class="fa-solid fa-envelope"></i></h2>
          <p class="text-lg italic text-center">Send me your questions, Concerns, or Appeal Account restricted!</p>
        </div>
      </div>

      <!-- Contact Form Section -->
      <div class="flex-1 py-10 px-4 ">
        <div class="max-w-4xl mx-auto space-y-12 shadow-md shadow-black">
          <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8 space-y-6">
              <div class="space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Your name</label>
                <input
                  id="name"
                  v-model="form.name"
                  class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
                  placeholder="Enter your name"
                />
              </div>
              <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">Your email</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
                  placeholder="Enter your email"
                />
              </div>
              <div class="space-y-2">
                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                <input
                  id="subject"
                  v-model="form.subject"
                  class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400"
                  placeholder="Enter subject"
                />
              </div>
              <div class="space-y-2">
                <label for="message" class="block text-sm font-medium text-gray-700">Your message</label>
                <textarea
                  id="message"
                  v-model="form.message"
                  class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-400 min-h-[150px]"
                  placeholder="Enter your message"
                ></textarea>
              </div>
              <button
                @click="submitForm"
                class="w-full bg-slate-800 text-white py-3 rounded-md hover:bg-yellow-500 hover:text-black transition duration-300"
                :disabled="isLoading"
              >
                <span v-if="isLoading">Submitting...</span>
                <span v-else>SUBMIT <i class="fa-solid fa-paper-plane"></i></span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2'; // Import SweetAlert2

export default {
  name: 'ContactUs',
  data() {
    return {
      form: {
        name: '',
        email: '',
        subject: '',
        message: '',
      },
      successMessage: '',
      errorMessage: '',
      isLoading: false, // Loading state
    };
  },
  methods: {
    async submitForm() {
      this.isLoading = true; // Start loading animation
      Swal.fire({
        title: 'Sending...',
        text: 'Please wait while we process your request.',
        imageUrl: 'https://i.pinimg.com/originals/d5/a2/b0/d5a2b01b8294bfb8678d67342b106795.gif', // Optional: Add your loading gif
        imageWidth: 100,
        imageHeight: 100,
        showConfirmButton: false,
        allowOutsideClick: false,
        willOpen: () => {
          Swal.showLoading();
        },
      });

      try {
        const response = await axios.post('http://localhost:3000/api/contact', this.form);
        this.successMessage = response.data.message;
        this.errorMessage = '';
        this.resetForm();
        Swal.fire({
          title: 'Success!',
          text: this.successMessage,
          icon: 'success',
          confirmButtonText: 'Ok',
        });
      } catch (error) {
        this.errorMessage = error.response?.data.message || 'An error occurred. Please try again later.';
        this.successMessage = '';
        Swal.fire({
          title: 'Error!',
          text: this.errorMessage,
          icon: 'error',
          confirmButtonText: 'Retry',
        });
      } finally {
        this.isLoading = false; // End loading animation
      }
    },
    resetForm() {
      this.form = { name: '', email: '', subject: '', message: '' };
    },
  },
};
</script>

<style scoped>
.contact {
  margin: 0;
  min-height: 100vh;
  background-image: url('../assets/img/bitch.jpg');
  background-size: cover;
  background-position: center;
}
</style>
