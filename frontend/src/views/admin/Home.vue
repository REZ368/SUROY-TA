<template>
  <div class="text-black p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto animate-fade-in">
    <!-- Header Section -->
    <div class="mb-8">
      <h2 class="text-3xl font-bold text-black mb-4">Welcome to the Admin Dashboard</h2>
      <p class="text-lg text-gray-900">This is the home page of your admin panel. Monitor and manage key metrics here.</p>
    </div>

    <!-- Dashboard Counts Cards Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card
        title="Users"
        :count="dashboardCounts.userCount"
        icon="👤"
        class="transition transform hover:scale-105 hover:shadow-lg hover:bg-blue-50"
      />
      <Card
        title="Destinations"
        :count="dashboardCounts.destinationCount"
        icon="📍"
        class="transition transform hover:scale-105 hover:shadow-lg hover:bg-green-50"
      />
      <Card
        title="Posts"
        :count="dashboardCounts.postCount"
        icon="📝"
        class="transition transform hover:scale-105 hover:shadow-lg hover:bg-yellow-50"
      />
      <Card
        title="Comments"
        :count="dashboardCounts.commentCount"
        icon="💬"
        class="transition transform hover:scale-105 hover:shadow-lg hover:bg-purple-50"
      />
    </div>

    <!-- Average Ratings Chart -->
    <div class="mt-8 shadow-md shadow-black p-4">
      <h3 class="text-2xl font-bold text-black mb-4">Average Ratings of Promotions</h3>
      <div class="w-full h-80">
        <Bar v-if="ratingChartData" :data="ratingChartData" :options="chartOptions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js';
import Card from '@/views/admin/Card.vue';
import axios from 'axios';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const dashboardCounts = ref({
  userCount: 0,
  destinationCount: 0,
  postCount: 0,
  commentCount: 0,
});

const ratingChartData = ref(null);
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: { enabled: true },
  },
});

// Fetch dashboard counts
const fetchDashboardCounts = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get('http://localhost:3000/api/dashboard/counts', {
      headers: { Authorization: `Bearer ${token}` },
    });
    dashboardCounts.value = response.data;
  } catch (error) {
    console.error('Error fetching dashboard counts:', error);
  }
};

// Fetch promotions and their average ratings
const fetchPromotionRatings = async () => {
  try {
    const token = localStorage.getItem('token');
    const response = await axios.get('http://localhost:3000/api/promotions/getallpromotions', {
      headers: { Authorization: `Bearer ${token}` },
    });

    const promotions = response.data;

    ratingChartData.value = {
      labels: promotions.map((promotion) => promotion.title),
      datasets: [
        {
          label: 'Average Rating',
          data: promotions.map((promotion) => parseFloat(promotion.averageRating)),
          backgroundColor: '#4CAF50',
        },
      ],
    };
  } catch (error) {
    console.error('Error fetching promotion ratings:', error);
  }
};

onMounted(() => {
  fetchDashboardCounts();
  fetchPromotionRatings();
});
</script>

<style scoped>
.home {
  background-color: #6da8aa; /* Light background color */
}

.card {
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
  transform: scale(1.05); /* Scale effect on hover */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Add a shadow effect on hover */
}
</style>
