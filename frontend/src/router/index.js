import { createRouter, createWebHistory } from 'vue-router';
import Swal from 'sweetalert2';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'homes',
      component: () => import('../views/HomeView.vue'),
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('../views/Contact.vue'),
    },
    {
      path: '/latestposts',
      name: 'latestposts',
      component: () => import('../views/LatestPosts.vue'),
    },
    {
      path: '/mostviewed',
      name: 'mostviewed',
      component: () => import('../views/MostViewed.vue'),
    },
    {
      path: '/destinations',
      name: 'destinations',
      component: () => import('../views/Destinations.vue'),
    },
    {
      path: '/promotionslists',
      name: 'promotionslists',
      component: () => import('../views/PromotionsLists.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/promotion/:id',
      name: 'promotionlists',
      component: () => import('../components/PromotionDetail.vue'),
      props: true, 
      meta: { requiresAuth: true, },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/auth/Register.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/Login.vue'),
    },
    {
      path: '/results',
      name: 'Results',
      component: () => import('../components/Results.vue'),
    },
    {
      path: '/posts',
      name: 'posts',
      component: () => import('../views/Posts.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/posts/:id',
      name: 'post-detail',
      component: () => import('../components/PostDetail.vue'),
      meta: { requiresAuth: true, },
    },
    {
      path: '/auth/callback',
      name: 'OAuthCallback',
      component: () => import('../views/OAuthCallback.vue'),
    },
    {
      path: '/my-destinations',
      name: 'my-destinations',
      component: () => import('../components/MyDestinations.vue'),
      meta: { requiresAuth: true, requiresRegistration: true, },
    },
    {
      path: '/profile',
      name: 'my-profile',
      component: () => import('../views/Profile.vue'),
      meta: { requiresAuth: true, requiresRegistration: true, },
    },
    {
      path: '/my-promotions',
      name: 'my-promotions',
      component: () => import('../components/MyPromotions.vue'),
      meta: { requiresAuth: true, requiresRegistration: true, },
    },
    {
      path: '/create-post',
      name: 'create-post',
      component: () => import('../components/Create.vue'),
      meta: { 
        requiresAuth: true,
        requiresRegistration: true,
      }, 
    },
    {
      path: '/promote',
      name: 'promote',
      component: () => import('../components/Promote.vue'),
      meta: { requiresAuth: true ,   requiresRegistration: true,}, // Protect this route
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/Profile.vue'),
      meta: { requiresAuth: true, requiresRegistration: true, },
    },
    {
      path: '/destination/:destination',
      name: 'destination-posts',
      component: () => import('../components/Destinationposts.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/category/:category',
      name: 'category-posts',
      component: () => import('../components/Categoryposts.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/authors/:id',
      name: 'AuthorProfile',
      component: () => import('../components/AuthorProfile.vue'),
      meta: { requiresAuth: true , requiresRegistration: true,},
    },
    {
      path: '/dashboard/',
      name: 'dashboard',
      component: () => import('../views/admin/Dashboard.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }, // Protect this route for Admins
      children: [
        {
          path: 'home',
          name: 'home',
          component: () => import('../views/admin/Home.vue'), // Child component
        },
        {
          path: 'users',
          name: 'dashboard-users',
          component: () => import('../views/admin/Users.vue'), // Child component
        },
        {
          path: 'posts',
          name: 'dashboard-posts',
          component: () => import('../views/admin/Posts.vue'), // Child component
        },
        {
          path: 'promotions',
          name: 'dashboard-promotions',
          component: () => import('../views/admin/Promotions.vue'), // Child component
        },
        {
          path: 'reports',
          name: 'dashboard-reports',
          component: () => import('../views/admin/Reports.vue'), // Child component
        },
        {
          path: 'comments',
          name: 'dashboard-comments',
          component: () => import('../views/admin/Comments.vue'), // Child component
        },
        {
          path: 'categories',
          name: 'dashboard-categories',
          component: () => import('../views/admin/Categories.vue'), // Child component
        },
        {
          path: 'destinations',
          name: 'dashboard-destinations',
          component: () => import('../views/admin/Destinations.vue'), // Child component
        },
        {
          path: 'settings',
          name: 'dashboard-settings',
          component: () => import('../views/admin/Settings.vue'), // Child component
        },
      ],
    },
  ],
});


router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem('token'); // Check if the user is logged in
  const userType = localStorage.getItem('userType'); // Get the user type from local storage

  // Handle the case where a logged-in user tries to access login or register pages
  if (to.name === 'register' || to.name === 'login') {
    if (isLoggedIn) {
      return next({ name: 'homes' }); // Redirect logged-in users to home
    }
  }

  // Check if the route requires authentication
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: 'login' }); // Redirect to login if not authenticated
  }

  // Check if the user is a guest and tries to access routes that require registration
  if (userType === 'Guest' && to.meta.requiresRegistration) {
    Swal.fire('Access Denied', 'You must register to access this page.', 'error'); // Show the error message
    return next({ name: 'login' }); // Redirect to login or registration page
  }

  // Check if the route requires Admin permissions
  if (to.meta.requiresAdmin && userType !== 'Admin') {
    return next({ name: 'homes' }); // Redirect to home if the user is not an Admin
  }

  // Allow the route navigation to proceed
  next();
});


export default router;