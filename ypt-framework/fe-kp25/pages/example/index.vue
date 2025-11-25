<script setup lang="ts">
import { useCounter } from "~/composables/useCounter";
import { capitalizeFirstLetter } from "~/helpers/capitalize";
import { get } from "~/lib/axios/api-bridge";

const { count, increment, decrement, reset } = useCounter();

const title = "composable & helper nuxt 3";
const capitalizedTitle = capitalizeFirstLetter(title);

const handleGetData = async () => {
  try {
    const { status, data } = await get("posts");
    console.log(status, data);
  } catch (error) {
    console.error(error);
  }
};

onMounted(() => {
  handleGetData();
});
</script>

<template>
  <div
    class="container d-flex justify-content-center align-items-center min-vh-100"
  >
    <div
      class="card shadow-lg border-0 rounded-4 p-4"
      style="max-width: 80vw; width: 100%"
    >
      <div class="card-body text-center">
        <h1 class="mb-4 fw-bold text-primary">
          <i class="fas fa-magic me-2"></i>
          {{ capitalizedTitle }}
        </h1>

        <div class="bg-light rounded-3 py-3 px-4 mb-4">
          <h5 class="text-muted">Counter Sekarang</h5>
          <p class="display-4 fw-bold text-primary mb-0">{{ count }}</p>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-3 mb-3">
          <button
            class="btn btn-outline-success btn-lg px-4"
            @click="increment"
          >
            + Tambah
          </button>
          <button class="btn btn-danger btn-lg px-4" @click="decrement">
            - Kurang
          </button>
        </div>

        <button class="btn btn-secondary btn-sm" @click="reset">
          Reset Nilai
        </button>
      </div>
    </div>
  </div>
</template>
