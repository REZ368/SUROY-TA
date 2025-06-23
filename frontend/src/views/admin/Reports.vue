<template>
  <div class="p-4 rounded-lg w-full md:w-[calc(100%-16rem)] ml-auto animate-fade-in">
    <h1 class="text-3xl font-bold text-black mb-6">Reports Management</h1>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-slate-900 text-white">
          <tr>
            <th class="p-4">Report ID</th>
            <th class="p-4">Username</th>
            <th class="p-4">Destination</th>
            <th class="p-4">Reason</th>
            <th class="p-4">Status</th>
            <th class="p-4">Created At</th>
            <th class="p-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="report in reports"
            :key="report.report_id"
            class="border-b hover:bg-gray-50 transition"
          >
            <td class="p-4">{{ report.report_id }}</td>
            <td class="p-4">{{ report.username }}</td>
            <td class="p-4">{{ report.post_title || "N/A" }}</td>
            <td class="p-4">{{ report.reason }}</td>
            <td class="p-4">
              <span
                class="px-2 py-1 rounded-md text-white"
                :class="{
                  'bg-blue-500': report.status === 'pending',
                  'bg-green-500': report.status === 'resolved',
                  'bg-red-500': report.status === 'ignored',
                }"
              >
                {{ report.status }}
              </span>
            </td>
            <td class="p-4">{{ new Date(report.created_at).toLocaleString() }}</td>
            <td class="p-4 space-x-2">
              <button
                class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600"
                @click="viewReport(report)"
              >
                View
              </button>
              <button
                class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600"
                @click="openUpdateStatusModal(report)"
              >
                Update Status
              </button>
            </td>
          </tr>
          <tr v-if="reports.length === 0">
            <td class="p-4 text-center text-gray-500" colspan="7">
              No reports available.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div class="flex justify-between items-center mt-4">
      <button
        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400"
        :disabled="currentPage === 1"
        @click="prevPage"
      >
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button
        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400"
        :disabled="currentPage === totalPages"
        @click="nextPage"
      >
        Next
      </button>
    </div>

    <!-- Update Status Modal -->
    <div
      v-if="showUpdateStatusModal"
      class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <h3 class="text-lg font-bold text-teal-700 mb-4">Update Report Status</h3>
        <p><strong>Report ID:</strong> {{ selectedReport?.report_id }}</p>
        <p><strong>Current Status:</strong> {{ selectedReport?.status }}</p>
        <div class="mt-4">
          <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Select New Status:</label>
          <select
            v-model="newStatus"
            id="status"
            class="border rounded-md px-2 py-1 w-full"
          >
            <option value="Pending">Pending</option>
            <option value="Resolved">Resolved</option>
            <option value="Ignored">Ignored</option>
          </select>
        </div>
        <div class="flex justify-end space-x-2 mt-6">
          <button
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md"
            @click="closeUpdateStatusModal"
          >
            Cancel
          </button>
          <button
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            @click="updateStatus(selectedReport?.report_id)"
          >
            Save
          </button>
        </div>
      </div>
    </div>

    <!-- View Report Modal -->
    <div
      v-if="showViewReportModal"
      class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h3 class="text-lg font-bold text-teal-700 mb-4">View Report</h3>
        <p><strong>Report ID:</strong> {{ selectedReport?.report_id }}</p>
        <p><strong>Username:</strong> {{ selectedReport?.username }}</p>
        <p><strong>Post Title:</strong> {{ selectedReport?.post_title || 'N/A' }}</p>
        <p><strong>Reason:</strong> {{ selectedReport?.reason }}</p>
        <p><strong>Status:</strong> {{ selectedReport?.status }}</p>
        <p><strong>Created At:</strong> {{ new Date(selectedReport?.created_at).toLocaleString() }}</p>

        <div class="flex justify-end mt-4">
          <button
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md"
            @click="closeViewReportModal"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const reports = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const pageSize = 5;
const showUpdateStatusModal = ref(false);
const showViewReportModal = ref(false); // New flag for viewing report
const selectedReport = ref(null);
const newStatus = ref("");

// Fetch paginated reports
const fetchReports = async () => {
  try {
    const response = await axios.get("http://localhost:3000/api/reports/getall", {
      params: { page: currentPage.value, limit: pageSize },
    });

    reports.value = response.data.reports || [];
    currentPage.value = response.data.currentPage;
    totalPages.value = response.data.totalPages;
  } catch (error) {
    console.error("Error fetching reports:", error);
    reports.value = [];
  }
};

// Open Update Status Modal
const openUpdateStatusModal = (report) => {
  selectedReport.value = report;
  newStatus.value = report.status;
  showUpdateStatusModal.value = true;
};

// Close Update Status Modal
const closeUpdateStatusModal = () => {
  showUpdateStatusModal.value = false;
  selectedReport.value = null;
  newStatus.value = "";
};

// Open View Report Modal
const viewReport = (report) => {
  selectedReport.value = report;
  showViewReportModal.value = true;
};

// Close View Report Modal
const closeViewReportModal = () => {
  showViewReportModal.value = false;
  selectedReport.value = null;
};

// Update report status
const updateStatus = async (reportId) => {
  try {
    await axios.put(`http://localhost:3000/api/reports/${reportId}/status`, { status: newStatus.value });

    // Show success notification
    Swal.fire({
      icon: "success",
      title: "Status Updated",
      text: "Report status updated successfully!",
      confirmButtonColor: "#3085d6",
    });

    closeUpdateStatusModal();
    fetchReports();
  } catch (error) {
    console.error("Error updating status:", error);

    // Show error notification
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to update report status. Please try again.",
      confirmButtonColor: "#d33",
    });
  }
};

// Pagination controls
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchReports();
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchReports();
  }
};

// Fetch initial data
onMounted(() => {
  fetchReports();
});
</script>

