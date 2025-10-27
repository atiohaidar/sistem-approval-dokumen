<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Header Top - Glassmorphism -->
    <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-white/10 border-b border-white/20">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo with Glow Effect -->
          <div class="flex items-center space-x-3 group">
            <div class="relative">
              <div class="absolute inset-0 bg-telkom-red rounded-full blur-lg opacity-50 group-hover:opacity-75 transition"></div>
              <div class="relative w-12 h-12 bg-gradient-to-br from-telkom-red to-telkom-red-dark rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white font-bold text-xl">T</span>
              </div>
            </div>
            <div>
              <h1 class="text-xl font-bold text-white">Sistem Approval Dokumen</h1>
              <p class="text-sm text-gray-300">YPT</p>
            </div>
          </div>

          <!-- Search & Social -->
          <div class="flex items-center space-x-4">
            <div class="hidden md:flex items-center">
              <input 
                type="text" 
                placeholder="Search..." 
                class="px-4 py-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-telkom-red focus:border-transparent transition"
              />
              <button class="ml-2 p-2 text-gray-300 hover:text-telkom-red transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
            
            <!-- Social Icons with Hover Effect -->
            <div class="flex space-x-2">
              <a href="#" class="w-10 h-10 bg-blue-600/20 backdrop-blur-md border border-blue-500/30 rounded-lg flex items-center justify-center hover:bg-blue-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-blue-400 hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a href="#" class="w-10 h-10 bg-red-600/20 backdrop-blur-md border border-red-500/30 rounded-lg flex items-center justify-center hover:bg-red-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-red-400 hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a>
              <a href="#" class="w-10 h-10 bg-pink-600/20 backdrop-blur-md border border-pink-500/30 rounded-lg flex items-center justify-center hover:bg-pink-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-pink-400 hover:text-white transition" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation Bar - Glassmorphism -->
    <nav class="fixed top-20 left-0 right-0 z-40 backdrop-blur-md bg-gradient-to-r from-telkom-red/90 to-telkom-red-dark/90 shadow-2xl border-b border-white/10">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
          <div class="flex space-x-2">
            <a href="#" class="text-white px-6 py-4 border-b-2 border-yellow-400 hover:bg-white/10 transition-all font-medium relative group">
              <span class="relative z-10">Home</span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
            </a>
            <a href="#tentang" class="text-white/80 px-6 py-4 border-b-2 border-transparent hover:border-yellow-400 hover:bg-white/10 hover:text-white transition-all font-medium relative group">
              <span class="relative z-10">Tentang Sistem</span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
            </a>
            <a href="#fitur" class="text-white/80 px-6 py-4 border-b-2 border-transparent hover:border-yellow-400 hover:bg-white/10 hover:text-white transition-all font-medium relative group">
              <span class="relative z-10">Fitur</span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
            </a>
            <a href="#panduan" class="text-white/80 px-6 py-4 border-b-2 border-transparent hover:border-yellow-400 hover:bg-white/10 hover:text-white transition-all font-medium relative group">
              <span class="relative z-10">Panduan</span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
            </a>
          </div>
          
          <NuxtLink 
            to="/login" 
            class="relative group bg-white text-telkom-red px-8 py-2.5 rounded-lg font-semibold hover:bg-yellow-400 hover:text-white transition-all duration-300 shadow-lg hover:shadow-yellow-400/50 overflow-hidden"
          >
            <span class="relative z-10">Login</span>
            <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-yellow-500 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
          </NuxtLink>
        </div>
      </div>
    </nav>

    <!-- Hero Section - Modern & Futuristic -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-32">
      <!-- Animated Background -->
      <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-telkom-red/20 via-purple-900/20 to-blue-900/20"></div>
        <div class="absolute top-20 left-20 w-96 h-96 bg-telkom-red/30 rounded-full filter blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-blue-600/30 rounded-full filter blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/20 rounded-full filter blur-3xl animate-pulse delay-2000"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="grid md:grid-cols-2 gap-12 items-center">
          <!-- Left Content -->
          <div class="text-white space-y-8 animate-fade-in-up">
            <div class="inline-block">
              <span class="text-yellow-400 text-sm font-semibold tracking-widest uppercase px-4 py-2 bg-yellow-400/10 backdrop-blur-sm rounded-full border border-yellow-400/30">
                Welcome to the Future
              </span>
            </div>
            
            <h1 class="text-6xl md:text-7xl font-black leading-tight">
              <span class="bg-gradient-to-r from-white via-gray-200 to-gray-400 bg-clip-text text-transparent">
                SISTEM
              </span>
              <br/>
              <span class="bg-gradient-to-r from-telkom-red via-red-500 to-orange-500 bg-clip-text text-transparent animate-gradient">
                APPROVAL
              </span>
              <br/>
              <span class="bg-gradient-to-r from-blue-400 via-cyan-400 to-teal-400 bg-clip-text text-transparent animate-gradient">
                DOKUMEN
              </span>
            </h1>
            
            <div class="h-1 w-40 bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 rounded-full"></div>
            
            <p class="text-xl text-gray-300 leading-relaxed max-w-xl">
              Platform approval dokumen digital dengan teknologi blockchain-ready, 
              AI-powered validation, dan sistem keamanan multi-layer untuk 
              <span class="text-telkom-red font-semibold">YPT</span>
            </p>
            
            <div class="flex flex-wrap gap-4 pt-4">
              <NuxtLink 
                to="/login" 
                class="group relative px-8 py-4 bg-gradient-to-r from-telkom-red to-orange-600 rounded-xl font-bold text-white shadow-2xl shadow-telkom-red/50 hover:shadow-telkom-red overflow-hidden transition-all duration-300 hover:scale-105"
              >
                <span class="relative z-10 flex items-center gap-2">
                  MULAI SEKARANG
                  <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                  </svg>
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-red-700 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
              </NuxtLink>
              
              <a 
                href="#fitur" 
                class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-xl font-bold text-white hover:bg-white/20 transition-all duration-300 hover:scale-105"
              >
                Pelajari Lebih Lanjut
              </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-6 pt-8">
              <div class="text-center">
                <div class="text-3xl font-black bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent">500+</div>
                <div class="text-sm text-gray-400">Users</div>
              </div>
              <div class="text-center">
                <div class="text-3xl font-black bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">5K+</div>
                <div class="text-sm text-gray-400">Documents</div>
              </div>
              <div class="text-center">
                <div class="text-3xl font-black bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">99.9%</div>
                <div class="text-sm text-gray-400">Uptime</div>
              </div>
            </div>
          </div>

          <!-- Right Content - 3D Floating Card -->
          <div class="relative animate-fade-in-up delay-300">
            <div class="relative perspective-1000">
              <!-- Main Card -->
              <div class="relative transform hover:scale-105 transition-transform duration-500">
                <!-- Glow Effect -->
                <div class="absolute inset-0 bg-gradient-to-br from-telkom-red via-purple-600 to-blue-600 rounded-3xl blur-2xl opacity-50 animate-pulse"></div>
                
                <!-- Glass Card -->
                <div class="relative bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-3xl p-12 border border-white/20 shadow-2xl">
                  <!-- Icon -->
                  <div class="w-32 h-32 mx-auto mb-8 bg-gradient-to-br from-telkom-red to-orange-600 rounded-3xl flex items-center justify-center shadow-2xl shadow-telkom-red/50 transform hover:rotate-12 transition-transform duration-500">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  
                  <!-- Text -->
                  <div class="text-center space-y-2">
                    <h3 class="text-4xl font-black bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
                      Digital
                    </h3>
                    <h2 class="text-5xl font-black bg-gradient-to-r from-telkom-red to-orange-500 bg-clip-text text-transparent">
                      Approval
                    </h2>
                    <p class="text-gray-400 text-lg">Next Generation System</p>
                  </div>

                  <!-- Feature Pills -->
                  <div class="flex flex-wrap gap-2 justify-center mt-8">
                    <span class="px-4 py-2 bg-telkom-red/20 border border-telkom-red/30 rounded-full text-xs text-red-300 font-semibold backdrop-blur-sm">
                      🔐 Secure
                    </span>
                    <span class="px-4 py-2 bg-blue-600/20 border border-blue-500/30 rounded-full text-xs text-blue-300 font-semibold backdrop-blur-sm">
                      ⚡ Fast
                    </span>
                    <span class="px-4 py-2 bg-green-600/20 border border-green-500/30 rounded-full text-xs text-green-300 font-semibold backdrop-blur-sm">
                      ✓ Reliable
                    </span>
                  </div>
                </div>
              </div>

              <!-- Floating Elements -->
              <div class="absolute -top-4 -right-4 w-24 h-24 bg-yellow-400/20 backdrop-blur-md rounded-2xl border border-yellow-400/30 flex items-center justify-center animate-float">
                <svg class="w-12 h-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              
              <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-blue-500/20 backdrop-blur-md rounded-2xl border border-blue-500/30 flex items-center justify-center animate-float delay-500">
                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <a href="#fitur" class="flex flex-col items-center gap-2 text-white/50 hover:text-white transition">
          <span class="text-xs">Scroll</span>
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </a>
      </div>
    </section>

    <!-- Features Section - Modern Cards -->
    <section id="fitur" class="py-32 bg-gradient-to-b from-gray-900 to-gray-800 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-20 animate-fade-in-up">
          <span class="text-telkom-red text-sm font-semibold tracking-widest uppercase">Our Features</span>
          <h2 class="text-5xl md:text-6xl font-black text-white mt-4 mb-6">
            <span class="bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
              Fitur Unggulan
            </span>
          </h2>
          <div class="w-32 h-1 bg-gradient-to-r from-telkom-red to-orange-500 mx-auto mb-6 rounded-full"></div>
          <p class="text-gray-400 text-xl max-w-3xl mx-auto">
            Teknologi terdepan untuk sistem approval dokumen yang aman, cepat, dan reliable
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feature 1 - Multi-Level Approval -->
          <div class="group relative animate-fade-in-up">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-telkom-red/50 to-orange-600/50 rounded-3xl blur-xl opacity-0 group-hover:opacity-75 transition-opacity duration-500"></div>
            
            <!-- Card -->
            <div class="relative bg-gradient-to-br from-white/5 to-white/[0.02] backdrop-blur-xl rounded-3xl p-8 border border-white/10 hover:border-telkom-red/50 transition-all duration-500 h-full">
              <!-- Icon -->
              <div class="relative mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-telkom-red to-orange-600 rounded-2xl flex items-center justify-center shadow-2xl shadow-telkom-red/50 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                  <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <!-- Floating Badge -->
                <div class="absolute -top-2 -right-2 bg-yellow-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
                  NEW
                </div>
              </div>

              <!-- Content -->
              <h3 class="text-2xl font-bold text-white mb-4 group-hover:text-telkom-red transition-colors">
                Multi-Level Approval
              </h3>
              <p class="text-gray-400 leading-relaxed mb-6">
                Sistem approval bertingkat dengan konfigurasi fleksibel hingga 10 level. Support parallel dan sequential approval.
              </p>

              <!-- Features List -->
              <ul class="space-y-2">
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  10 Level Maximum
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Flexible Configuration
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Real-time Updates
                </li>
              </ul>

              <!-- Arrow -->
              <div class="absolute bottom-8 right-8 w-10 h-10 bg-telkom-red/10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <svg class="w-5 h-5 text-telkom-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Feature 2 - Digital Signature -->
          <div class="group relative animate-fade-in-up delay-200">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/50 to-cyan-500/50 rounded-3xl blur-xl opacity-0 group-hover:opacity-75 transition-opacity duration-500"></div>
            
            <div class="relative bg-gradient-to-br from-white/5 to-white/[0.02] backdrop-blur-xl rounded-3xl p-8 border border-white/10 hover:border-blue-500/50 transition-all duration-500 h-full">
              <div class="relative mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-2xl shadow-blue-500/50 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                  <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div class="absolute -top-2 -right-2 bg-cyan-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
                  SECURE
                </div>
              </div>

              <h3 class="text-2xl font-bold text-white mb-4 group-hover:text-blue-400 transition-colors">
                Digital Signature
              </h3>
              <p class="text-gray-400 leading-relaxed mb-6">
                QR Code dan watermark otomatis dengan enkripsi tingkat tinggi. Blockchain-ready untuk audit trail yang tidak dapat diubah.
              </p>

              <ul class="space-y-2">
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  QR Code Generation
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Auto Watermark
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Blockchain Ready
                </li>
              </ul>

              <div class="absolute bottom-8 right-8 w-10 h-10 bg-blue-500/10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Feature 3 - Real-time Tracking -->
          <div class="group relative animate-fade-in-up delay-400">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-500/50 to-pink-500/50 rounded-3xl blur-xl opacity-0 group-hover:opacity-75 transition-opacity duration-500"></div>
            
            <div class="relative bg-gradient-to-br from-white/5 to-white/[0.02] backdrop-blur-xl rounded-3xl p-8 border border-white/10 hover:border-purple-500/50 transition-all duration-500 h-full">
              <div class="relative mb-6">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-2xl shadow-purple-500/50 group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                  <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="absolute -top-2 -right-2 bg-pink-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
                  FAST
                </div>
              </div>

              <h3 class="text-2xl font-bold text-white mb-4 group-hover:text-purple-400 transition-colors">
                Real-time Tracking
              </h3>
              <p class="text-gray-400 leading-relaxed mb-6">
                Pantau status approval dokumen secara real-time dengan dashboard analytics. Push notification untuk setiap update.
              </p>

              <ul class="space-y-2">
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Live Dashboard
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Push Notifications
                </li>
                <li class="flex items-center gap-2 text-sm text-gray-500">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Analytics Reports
                </li>
              </ul>

              <div class="absolute bottom-8 right-8 w-10 h-10 bg-purple-500/10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- CTA Section -->
        <div class="text-center mt-20 animate-fade-in-up delay-600">
          <NuxtLink 
            to="/login" 
            class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-telkom-red via-orange-600 to-red-600 rounded-2xl font-bold text-white shadow-2xl shadow-telkom-red/50 hover:shadow-telkom-red hover:scale-105 transition-all duration-300"
          >
            <span class="text-lg">Coba Sekarang</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Footer - Modern -->
    <footer class="bg-gradient-to-b from-gray-900 to-black text-white py-16 relative overflow-hidden">
      <!-- Background Pattern -->
      <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 50px 50px;"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="grid md:grid-cols-4 gap-12 mb-12">
          <!-- Column 1 - Brand -->
          <div class="space-y-4">
            <div class="flex items-center space-x-3 mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-telkom-red to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
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
            <div class="flex space-x-3 pt-4">
              <a href="#" class="w-10 h-10 bg-blue-600/20 border border-blue-500/30 rounded-lg flex items-center justify-center hover:bg-blue-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
              </a>
              <a href="#" class="w-10 h-10 bg-red-600/20 border border-red-500/30 rounded-lg flex items-center justify-center hover:bg-red-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </a>
              <a href="#" class="w-10 h-10 bg-pink-600/20 border border-pink-500/30 rounded-lg flex items-center justify-center hover:bg-pink-600 hover:scale-110 transition-all duration-300">
                <svg class="w-5 h-5 text-pink-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
            </div>
          </div>

          <!-- Column 2 - Quick Links -->
          <div>
            <h4 class="font-bold text-lg mb-6 bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
              Quick Links
            </h4>
            <ul class="space-y-3">
              <li>
                <a href="#" class="text-gray-400 hover:text-white transition flex items-center gap-2 group">
                  <svg class="w-4 h-4 text-telkom-red group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Home
                </a>
              </li>
              <li>
                <a href="#tentang" class="text-gray-400 hover:text-white transition flex items-center gap-2 group">
                  <svg class="w-4 h-4 text-telkom-red group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Tentang
                </a>
              </li>
              <li>
                <a href="#fitur" class="text-gray-400 hover:text-white transition flex items-center gap-2 group">
                  <svg class="w-4 h-4 text-telkom-red group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Fitur
                </a>
              </li>
              <li>
                <NuxtLink to="/login" class="text-gray-400 hover:text-white transition flex items-center gap-2 group">
                  <svg class="w-4 h-4 text-telkom-red group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  Login
                </NuxtLink>
              </li>
            </ul>
          </div>

          <!-- Column 3 - Contact -->
          <div>
            <h4 class="font-bold text-lg mb-6 bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
              Hubungi Kami
            </h4>
            <ul class="space-y-3">
              <li class="flex items-start gap-3 text-gray-400">
                <svg class="w-5 h-5 text-telkom-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-sm">support@telkom.co.id</span>
              </li>
              <li class="flex items-start gap-3 text-gray-400">
                <svg class="w-5 h-5 text-telkom-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span class="text-sm">(021) 1234-5678</span>
              </li>
              <li class="flex items-start gap-3 text-gray-400">
                <svg class="w-5 h-5 text-telkom-red mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm">Jakarta, Indonesia</span>
              </li>
            </ul>
          </div>

          <!-- Column 4 - Newsletter -->
          <div>
            <h4 class="font-bold text-lg mb-6 bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">
              Newsletter
            </h4>
            <p class="text-gray-400 text-sm mb-4">
              Dapatkan update terbaru tentang fitur dan pengumuman sistem.
            </p>
            <div class="flex gap-2">
              <input 
                type="email" 
                placeholder="Email Anda" 
                class="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-telkom-red text-sm"
              />
              <button class="px-4 py-2 bg-gradient-to-r from-telkom-red to-orange-600 rounded-lg hover:from-orange-600 hover:to-red-700 transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="h-px bg-gradient-to-r from-transparent via-white/20 to-transparent mb-8"></div>

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

      <!-- Decorative Elements -->
      <div class="absolute top-0 right-0 w-96 h-96 bg-telkom-red/10 rounded-full filter blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600/10 rounded-full filter blur-3xl"></div>
    </footer>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

// Redirect to dashboard if authenticated
const auth = useAuthStore()
try {
  await auth.fetchUser()
} catch (_) {}
if (auth.user) {
  navigateTo('/dashboard')
}
</script>
