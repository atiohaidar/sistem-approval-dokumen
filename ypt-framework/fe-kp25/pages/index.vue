<template>
  <div
    class="min-vh-100 d-flex justify-content-center align-items-center text-white animated-gradient"
  >
    <div class="container text-center">
      <h1 class="display-4 fw-semibold">Welcome to FYP Framework</h1>
      <h1 class="fw-light">(Nuxt + Bootstrap)</h1>
      <p class="lead">Get started building your awesome app.</p>
      <div class="d-flex justify-content-center align-items-center gap-2">
        <NuxtLink
          to="https://nuxt.com/"
          target="_blank"
          class="btn btn-primary btn-lg"
        >
          Learn More
        </NuxtLink>
        <NuxtLink to="/example" class="btn btn-secondary btn-lg">
          example Page
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animated-gradient {
  background: linear-gradient(135deg, #006f4a, #043f3a, #006f4a);
  background-size: 400% 400%;
  animation: gradientMove 10s ease infinite;
}

@keyframes gradientMove {
  0% {
    background-position: 0 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0 50%;
  }
}
</style>
<script setup lang="ts"></script>
