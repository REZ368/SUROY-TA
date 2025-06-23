<template>
  <div class="mt-6 sm:mt-10 max-w-full sm:max-w-4xl mx-auto bg-white shadow-md shadow-black rounded-lg p-2 sm:p-6 animate-fade-in">
    <!-- Section Title -->
    <h2 class="text-xl sm:text-2xl font-bold mb-3 sm:mb-4 text-teal-600">Promotion Comments</h2>

    <!-- Comment Form -->
    <div>
      <form @submit.prevent="submitComment" class="mb-4 sm:mb-6">
        <textarea
          v-model="newComment"
          rows="3"
          placeholder="Write a comment..."
          class="w-full p-2 sm:p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm sm:text-base"
        ></textarea>
        <button
          type="submit"
          :disabled="isSubmitting || !newComment.trim()"
          class="mt-2 w-full sm:w-auto bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition duration-300 text-sm sm:text-base"
        >
          {{ isSubmitting ? "Submitting..." : "Submit Comment" }}
        </button>
      </form>
    </div>

    <!-- Comments List -->
    <div v-if="comments.length > 0">
      <div
        v-for="comment in comments"
        :key="comment.id"
        class="mb-4 border-b pb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between"
      >
        <div class="flex items-center w-full sm:w-auto">
          <!-- Profile Photo -->
          <img
            :src="comment.profilePhoto"
            alt="Profile Photo"
            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full mr-2 sm:mr-3"
          />
          <!-- Comment Details -->
          <div class="p-1 sm:p-2">
            <p class="font-semibold text-gray-700 text-sm sm:text-base">{{ comment.username }}</p>
            <p class="text-gray-600 text-sm sm:text-base break-words max-w-xs sm:max-w-md">{{ comment.content }}</p>
            <p class="text-xs sm:text-sm text-gray-400">{{ formatDate(comment.createdAt) }}</p>
            <div v-if="comment.userId === userId">
              <button
                @click="openEditModal(comment)"
                class="text-blue-500 hover:underline mr-2 sm:mr-3 text-xs sm:text-sm"
              >
                Edit
              </button>
              <button
                @click="deleteComment(comment.id)"
                class="text-red-500 hover:underline text-xs sm:text-sm"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
        <!-- Report Button -->
        <button
          @click="openReportModal(comment.id)"
          class="text-red-600 hover:underline text-xs sm:text-sm font-semibold mt-2 sm:mt-0"
        >
          Report
        </button>
      </div>
    </div>
    <div v-else class="text-gray-500 text-sm sm:text-base">No comments yet. Be the first to comment!</div>

    <!-- Report Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 px-2">
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg w-full max-w-xs sm:max-w-md md:max-w-lg">
        <h3 class="text-base sm:text-lg font-bold text-teal-700 mb-3 sm:mb-4">Report Comment</h3>
        <textarea
          v-model="reportReason"
          placeholder="Enter your reason for reporting"
          class="w-full p-2 border border-gray-300 rounded-lg text-sm sm:text-base"
          rows="4"
        ></textarea>
        <div class="flex flex-col sm:flex-row justify-between mt-4 gap-2">
          <button
            @click="submitReport"
            class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-md text-sm sm:text-base"
          >
            Submit Report
          </button>
          <button
            @click="closeReportModal"
            class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm sm:text-base"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
    <!-- Edit Comment Modal -->
    <div
      v-if="isEditing"
      class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center px-2"
    >
      <div class="bg-white p-4 sm:p-6 rounded-lg w-full max-w-xs sm:max-w-md md:max-w-lg">
        <h3 class="text-base sm:text-lg font-bold mb-3 sm:mb-4">Edit Comment</h3>
        <textarea
          v-model="editingContent"
          rows="4"
          class="w-full p-2 border border-gray-300 rounded-md text-sm sm:text-base"
        ></textarea>
        <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2">
          <button
            @click="updateComment"
            class="w-full sm:w-auto px-4 py-2 bg-blue-500 text-white rounded-md mr-0 sm:mr-2 text-sm sm:text-base"
          >
            Save
          </button>
          <button
            @click="closeEditModal"
            class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm sm:text-base"
          >
            Cancel
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

const editingComment = ref(null); // The comment being edited
const editingContent = ref(''); // Content for the editing comment
const userId = parseInt(localStorage.getItem("userId")); // Dynamically set from localStorage

// Props
const props = defineProps({
  promotionId: {
    type: Number,
    required: true,
  },
});

// Reactive state
const comments = ref([]);
const newComment = ref("");
const isSubmitting = ref(false);
const isLoggedIn = ref(false);

// Report Modal State
const isModalOpen = ref(false);
const reportReason = ref("");
const currentCommentId = ref(null);

// Open the edit modal with the selected comment's content
const editComment = (comment) => {
  editingComment.value = comment;
  editingContent.value = comment.content;
};

// Cancel the edit
const cancelEdit = () => {
  editingComment.value = null;
  editingContent.value = '';
};

// Fetch comments
const fetchComments = async () => {
  try {
    const response = await axios.get(
      `http://localhost:3000/api/promotions/${props.promotionId}/getcomments`,
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      }
    );

    comments.value = response.data.comments.map((comment) => ({
      id: comment.comment_id,
      content: comment.content,
      createdAt: comment.comment_created_at,
      username: comment.username || "Anonymous",
      profilePhoto: comment.profile_photo
        ? `http://localhost:3000${comment.profile_photo.trim()}`
        : "https://example.com/default-profile-photo.jpg",
    }));

    isLoggedIn.value = true;
  } catch (error) {
    console.error("Error fetching comments:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to fetch comments. Please try again later.",
    });
  }
};


const updateComment = async () => {
  try {
    const response = await axios.put(
      `http://localhost:3000/api/promotion/comments/${editingComment.value.id}`,
      { content: editingContent.value },
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      }
    );
    console.log(response.data.message);

    // Update the comment in the list
    const index = comments.value.findIndex(
      (comment) => comment.id === editingComment.value.id
    );
    comments.value[index].content = editingContent.value;

    cancelEdit();
  } catch (error) {
    console.error('Error updating comment:', error);
  }
};

// Delete a comment
const deleteComment = async (commentId) => {
  try {
    const response = await axios.delete(
      `http://localhost:3000/api/promotion/comments/${commentId}`,
      {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      }
    );
    console.log(response.data.message);

    // Remove the deleted comment from the list
    comments.value = comments.value.filter((comment) => comment.id !== commentId);
  } catch (error) {
    console.error('Error deleting comment:', error);
  }
};

// Open Report Modal
const openReportModal = (commentId) => {
  currentCommentId.value = commentId;
  isModalOpen.value = true;
};

// Close Report Modal
const closeReportModal = () => {
  isModalOpen.value = false;
  reportReason.value = "";
};

// Submit Report
const submitReport = async () => {
  if (!reportReason.value.trim()) {
    Swal.fire({
      icon: "warning",
      title: "Missing Reason",
      text: "Please provide a reason for reporting the comment.",
    });
    return;
  }

  try {
    await axios.post(
      "http://localhost:3000/api/reports/create",
      {
        comment_id: currentCommentId.value,
        reason: reportReason.value,
      },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      }
    );

    Swal.fire({
      icon: "success",
      title: "Report Submitted",
      text: "Comment reported successfully. Admins will review you report and will take actions.",
    });

    closeReportModal();
  } catch (error) {
    console.error("Error reporting comment:", error);
    Swal.fire({
      icon: "error",
      title: "Report Failed",
      text: "Failed to report the comment. Please try again.",
    });
  }
};

// Submit comment
const submitComment = async () => {
  if (!newComment.value.trim()) return;

  isSubmitting.value = true;

  try {
    const response = await axios.post(
      `http://localhost:3000/api/promotions/${props.promotionId}/comment`,
      { content: newComment.value },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      }
    );

    const newCommentData = {
      id: response.data.comment_id,
      content: response.data.content,
      createdAt: response.data.comment_created_at,
      username: response.data.username || "Anonymous",
      profilePhoto: response.data.profile_photo
        ? `http://localhost:3000${response.data.profile_photo.trim()}`
        : "https://example.com/default-profile-photo.jpg",
    };

    comments.value.unshift(newCommentData);
    newComment.value = "";

    Swal.fire({
      icon: "success",
      title: "Comment Posted!",
      text: "Your comment was successfully posted",
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error("Error submitting comment:", error);
    Swal.fire({
      icon: "error",
      title: "Submission Failed",
      text: "Failed to submit your comment. Please try again later.",
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

// Lifecycle Hook
onMounted(() => {
  fetchComments();
});
</script>
