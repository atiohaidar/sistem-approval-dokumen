<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header - Flat Design -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex space-x-1">
            <a href="#" class="text-telkom-red px-6 py-4 border-b-2 border-telkom-red font-semibold transition-colors">
              Home
            </a>
            <a href="#tentang" class="text-gray-600 px-6 py-4 border-b-2 border-transparent hover:border-telkom-red hover:text-telkom-red font-medium transition-colors">
              Tentang Sistem
            </a>
            <a href="#fitur" class="text-gray-600 px-6 py-4 border-b-2 border-transparent hover:border-telkom-red hover:text-telkom-red font-medium transition-colors">
              Fitur
            </a>
            <a href="#panduan" class="text-gray-600 px-6 py-4 border-b-2 border-transparent hover:border-telkom-red hover:text-telkom-red font-medium transition-colors">
              Panduan
            </a>
          </div>
          
          <!-- User Menu for Authenticated Users -->
          <div v-if="auth.user" class="flex items-center gap-4">
            <div class="flex items-center gap-3 px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg">
              <div class="w-8 h-8 bg-telkom-red rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-sm">{{ getUserInitials(auth.user.name) }}</span>
              </div>
              <div class="text-gray-900">
                <div class="text-sm font-semibold">{{ auth.user.name }}</div>
                <div class="text-xs text-gray-600">{{ auth.user.role }}</div>
              </div>
            </div>
            <NuxtLink 
              to="/dashboard" 
              class="bg-telkom-red text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-telkom-red-dark transition-colors"
            >
              Dashboard
            </NuxtLink>
          </div>
          
          <!-- Login Button for Guest Users -->
          <NuxtLink 
            v-else
            to="/login" 
            class="bg-telkom-red text-white px-8 py-2.5 rounded-lg font-semibold hover:bg-telkom-red-dark transition-colors"
          >
            Login
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- Hero Section - Flat & Clean -->
    <section class="relative min-h-screen flex items-center justify-center bg-white pt-24">
      <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-16 items-center">
          <!-- Left Content -->
          <div class="space-y-6 animate-fade-in-up">
            <div class="inline-block">
              <span class="text-telkom-red text-sm font-semibold tracking-wider uppercase px-4 py-2 bg-red-50 rounded-md border border-red-100">
                <template v-if="auth.user">Welcome back, {{ auth.user.name.split(' ')[0] }}!</template>
                <template v-else>Sistem Approval Digital</template>
              </span>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-black leading-tight text-gray-900">
              SISTEM<br/>
              <span class="text-telkom-red">APPROVAL</span><br/>
              DOKUMEN
            </h1>
            
            <div class="h-1 w-32 bg-telkom-red"></div>
            
            <p class="text-lg text-gray-600 leading-relaxed max-w-xl">
              Platform approval dokumen digital dengan teknologi blockchain-ready, 
              AI-powered validation, dan sistem keamanan multi-layer untuk 
              <span class="text-telkom-red font-semibold">YPT</span>
            </p>
            
            <div class="flex flex-wrap gap-4 pt-4">
              <!-- Authenticated User Buttons -->
              <template v-if="auth.user">
                <NuxtLink 
                  to="/dashboard" 
                  class="px-8 py-4 bg-telkom-red rounded-lg font-bold text-white hover:bg-telkom-red-dark transition-colors inline-flex items-center gap-2"
                >
                  BUKA DASHBOARD
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                  </svg>
                </NuxtLink>
                
                <NuxtLink 
                  to="/documents/create" 
                  class="px-8 py-4 bg-white border-2 border-gray-300 rounded-lg font-bold text-gray-900 hover:border-telkom-red hover:text-telkom-red transition-colors"
                >
                  Upload Dokumen
                </NuxtLink>
              </template>
              
              <!-- Guest User Buttons -->
              <template v-else>
                <NuxtLink 
                  to="/login" 
                  class="px-8 py-4 bg-telkom-red rounded-lg font-bold text-white hover:bg-telkom-red-dark transition-colors inline-flex items-center gap-2"
                >
                  MULAI SEKARANG
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                  </svg>
                </NuxtLink>
                
                <a 
                  href="#fitur" 
                  class="px-8 py-4 bg-white border-2 border-gray-300 rounded-lg font-bold text-gray-900 hover:border-telkom-red hover:text-telkom-red transition-colors"
                >
                  Pelajari Lebih Lanjut
                </a>
              </template>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 pt-8">
              <div class="text-center">
                <div class="text-3xl font-black text-telkom-red">500+</div>
                <div class="text-sm text-gray-600 mt-1">Users</div>
              </div>
              <div class="text-center">
                <div class="text-3xl font-black text-telkom-red">5K+</div>
                <div class="text-sm text-gray-600 mt-1">Documents</div>
              </div>
              <div class="text-center">
                <div class="text-3xl font-black text-telkom-red">99.9%</div>
                <div class="text-sm text-gray-600 mt-1">Uptime</div>
              </div>
            </div>
          </div>

          <!-- Right Content - Illustration -->
          <div class="relative animate-fade-in-up delay-200">
            <div class="bg-white border border-gray-200 rounded-xl p-12 shadow-sm">
              <!-- Icon -->
              <div class="w-32 h-32 mx-auto mb-8 bg-telkom-red rounded-xl flex items-center justify-center">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              
              <!-- Text -->
              <div class="text-center space-y-2">
                <h3 class="text-3xl font-black text-gray-900">
                  Digital
                </h3>
                <h2 class="text-4xl font-black text-telkom-red">
                  Approval
                </h2>
                <p class="text-gray-600 text-lg">Next Generation System</p>
              </div>

              <!-- Feature Pills -->
              <div class="flex flex-wrap gap-2 justify-center mt-8">
                <span class="px-4 py-2 bg-red-50 border border-red-100 rounded-md text-xs text-telkom-red font-semibold">
                  🔐 Secure
                </span>
                <span class="px-4 py-2 bg-blue-50 border border-blue-100 rounded-md text-xs text-blue-600 font-semibold">
                  ⚡ Fast
                </span>
                <span class="px-4 py-2 bg-green-50 border border-green-100 rounded-md text-xs text-green-600 font-semibold">
                  ✓ Reliable
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Actions Section - Only for Authenticated Users -->
    <section v-if="auth.user" class="py-20 bg-gray-100">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <span class="text-telkom-red text-sm font-semibold tracking-wider uppercase">Quick Access</span>
          <h2 class="text-4xl md:text-5xl font-black text-gray-900 mt-4 mb-6">
            Aksi Cepat
          </h2>
          <div class="w-24 h-1 bg-telkom-red mx-auto mb-6"></div>
          <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Akses cepat ke fitur-fitur yang sering Anda gunakan
          </p>
        </div>

        <div class="grid md:grid-cols-4 gap-6">
          <!-- Upload Document -->
          <NuxtLink 
            to="/documents/create"
            class="bg-white border border-gray-200 rounded-lg p-6 hover:border-telkom-red hover:shadow-md transition-all"
          >
            <div class="w-16 h-16 bg-telkom-red rounded-lg flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Upload Dokumen</h3>
            <p class="text-gray-600 text-sm">Upload dokumen baru untuk approval</p>
          </NuxtLink>

          <!-- My Documents -->
          <NuxtLink 
            to="/documents"
            class="bg-white border border-gray-200 rounded-lg p-6 hover:border-blue-500 hover:shadow-md transition-all"
          >
            <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Dokumen Saya</h3>
            <p class="text-gray-600 text-sm">Lihat semua dokumen yang telah diupload</p>
          </NuxtLink>

          <!-- Pending Approvals -->
          <NuxtLink 
            to="/approvals"
            class="bg-white border border-gray-200 rounded-lg p-6 hover:border-yellow-500 hover:shadow-md transition-all"
          >
            <div class="w-16 h-16 bg-yellow-500 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Pending Approval</h3>
            <p class="text-gray-600 text-sm">Dokumen yang menunggu persetujuan Anda</p>
          </NuxtLink>

          <!-- Dashboard -->
          <NuxtLink 
            to="/dashboard"
            class="bg-white border border-gray-200 rounded-lg p-6 hover:border-purple-500 hover:shadow-md transition-all"
          >
            <div class="w-16 h-16 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Dashboard</h3>
            <p class="text-gray-600 text-sm">Lihat statistik dan ringkasan aktivitas</p>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-32 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-20">
          <span class="text-telkom-red text-sm font-semibold tracking-wider uppercase">Our Features</span>
          <h2 class="text-4xl md:text-5xl font-black text-gray-900 mt-4 mb-6">
            Fitur Unggulan
          </h2>
          <div class="w-32 h-1 bg-telkom-red mx-auto mb-6"></div>
          <p class="text-gray-600 text-xl max-w-3xl mx-auto">
            Teknologi terdepan untuk sistem approval dokumen yang aman, cepat, dan reliable
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feature 1 - Multi-Level Approval -->
          <div class="bg-white border border-gray-200 rounded-lg p-8 hover:shadow-md hover:border-telkom-red transition-all">
            <!-- Icon -->
            <div class="mb-6">
              <div class="w-16 h-16 bg-telkom-red rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <!-- Badge -->
              <span class="inline-block mt-4 bg-yellow-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-md">
                NEW
              </span>
            </div>

            <!-- Content -->
            <h3 class="text-2xl font-bold text-gray-900 mb-4">
              Multi-Level Approval
            </h3>
            <p class="text-gray-600 leading-relaxed mb-6">
              Sistem approval bertingkat dengan konfigurasi fleksibel hingga 10 level. Support parallel dan sequential approval.
            </p>

            <!-- Features List -->
            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                10 Level Maximum
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Flexible Configuration
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Real-time Updates
              </li>
            </ul>
          </div>

          <!-- Feature 2 - Digital Signature -->
          <div class="bg-white border border-gray-200 rounded-lg p-8 hover:shadow-md hover:border-blue-600 transition-all">
            <div class="mb-6">
              <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <span class="inline-block mt-4 bg-cyan-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-md">
                SECURE
              </span>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mb-4">
              Digital Signature
            </h3>
            <p class="text-gray-600 leading-relaxed mb-6">
              QR Code dan watermark otomatis dengan enkripsi tingkat tinggi. Blockchain-ready untuk audit trail yang tidak dapat diubah.
            </p>

            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                QR Code Generation
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Auto Watermark
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Blockchain Ready
              </li>
            </ul>
          </div>

          <!-- Feature 3 - Real-time Tracking -->
          <div class="bg-white border border-gray-200 rounded-lg p-8 hover:shadow-md hover:border-purple-600 transition-all">
            <div class="mb-6">
              <div class="w-16 h-16 bg-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <span class="inline-block mt-4 bg-pink-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-md">
                FAST
              </span>
            </div>

            <h3 class="text-2xl font-bold text-gray-900 mb-4">
              Real-time Tracking
            </h3>
            <p class="text-gray-600 leading-relaxed mb-6">
              Pantau status approval dokumen secara real-time dengan dashboard analytics. Push notification untuk setiap update.
            </p>

            <ul class="space-y-2">
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Live Dashboard
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Push Notifications
              </li>
              <li class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Analytics Reports
              </li>
            </ul>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center mt-20">
          <NuxtLink 
            v-if="auth.user"
            to="/dashboard" 
            class="inline-flex items-center gap-3 px-10 py-5 bg-telkom-red rounded-lg font-bold text-white hover:bg-telkom-red-dark transition-colors"
          >
            <span class="text-lg">Buka Dashboard</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </NuxtLink>
          <NuxtLink 
            v-else
            to="/login" 
            class="inline-flex items-center gap-3 px-10 py-5 bg-telkom-red rounded-lg font-bold text-white hover:bg-telkom-red-dark transition-colors"
          >
            <span class="text-lg">Coba Sekarang</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
      <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-12 mb-12">
          <!-- Column 1 - Brand -->
          <div class="space-y-4">
            <div class="flex items-center space-x-3 mb-6">
              <div class="w-12 h-12 bg-telkom-red rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-xl">T</span>
              </div>
              <div>
                <h3 class="text-lg font-bold">Sistem Approval</h3>
                <p class="text-sm text-gray-400">YPT</p>
              </div>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed">
              Solusi digital terdepan untuk manajemen approval dokumen dengan teknologi blockchain-ready dan AI-powered validation.
            </p>
          </div>

          <!-- Column 2 - Quick Links -->
          <div>
            <h4 class="font-bold text-lg mb-6">Quick Links</h4>
            <ul class="space-y-3">
              <li>
                <a href="#" class="text-gray-400 hover:text-white transition">Home</a>
              </li>
              <li>
                <a href="#tentang" class="text-gray-400 hover:text-white transition">Tentang</a>
              </li>
              <li>
                <a href="#fitur" class="text-gray-400 hover:text-white transition">Fitur</a>
              </li>
              <li>
                <NuxtLink to="/login" class="text-gray-400 hover:text-white transition">Login</NuxtLink>
              </li>
            </ul>
          </div>

          <!-- Column 3 - Contact -->
          <div>
            <h4 class="font-bold text-lg mb-6">Hubungi Kami</h4>
            <ul class="space-y-3">
              <li class="flex items-start gap-3 text-gray-400 text-sm">
                <svg class="w-5 h-5 text-telkom-red mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>support@telkom.co.id</span>
              </li>
              <li class="flex items-start gap-3 text-gray-400 text-sm">
                <svg class="w-5 h-5 text-telkom-red mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>(021) 1234-5678</span>
              </li>
              <li class="flex items-start gap-3 text-gray-400 text-sm">
                <svg class="w-5 h-5 text-telkom-red mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Jakarta, Indonesia</span>
              </li>
            </ul>
          </div>

          <!-- Column 4 - Newsletter -->
          <div>
            <h4 class="font-bold text-lg mb-6">Newsletter</h4>
            <p class="text-gray-400 text-sm mb-4">
              Dapatkan update terbaru tentang fitur dan pengumuman sistem.
            </p>
            <div class="flex gap-2">
              <input 
                type="email" 
                placeholder="Email Anda" 
                class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-telkom-red text-sm"
              />
              <button class="px-4 py-2 bg-telkom-red rounded-lg hover:bg-telkom-red-dark transition-colors">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="h-px bg-gray-800 mb-8"></div>

        <!-- Bottom Bar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-gray-400 text-sm">
            &copy; 2025 <span class="text-telkom-red font-semibold">YPT</span>. All rights reserved.
          </p>
          <div class="flex gap-6 text-sm text-gray-400">
            <a href="#" class="hover:text-white transition">Privacy Policy</a>
            <a href="#" class="hover:text-white transition">Terms of Service</a>
            <a href="#" class="hover:text-white transition">Cookies</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: false,
})

// Initialize auth store without redirecting
const auth = useAuthStore()
try {
  await auth.fetchUser()
} catch (_) {
  // Silently handle auth errors - user is not logged in
}

// Helper function to get user initials
const getUserInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
</script>
