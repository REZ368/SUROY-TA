<template>
     <!-- Header Section -->
  <header class="mt-20 mb-8 p-8 text-center">
    <nav class="flex justify-center space-x-8">
      <router-link
        to="/profile"
        class="text-white font-semibold hover:text-teal-600"
      > My Profile</router-link>
      <router-link
        to="/my-destinations"
        class="text-white font-semibold hover:text-teal-600"
      >
        My Destinations
      </router-link>
      <router-link
        to="/my-promotions"
        class="text-white font-semibold hover:text-teal-600"
      >
        My Promotions
      </router-link>
    </nav>
  </header>
</template>