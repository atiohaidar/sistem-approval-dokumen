<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
// Global app configuration
useSeoMeta({
  title: 'Sistem Approval Dokumen',
  description: 'Aplikasi approval dokumen digital dengan tanda tangan QR Code',
  ogTitle: 'Sistem Approval Dokumen',
  ogDescription: 'Aplikasi approval dokumen digital dengan tanda tangan QR Code',
})
</script>
