# Design System Documentation

## 📚 Panduan Lengkap Design System Modern & Futuristic

Design system ini dibuat berdasarkan landing page `index.vue` dan dapat digunakan di seluruh project untuk konsistensi visual yang maksimal.

---

## 🎨 1. CSS Utilities

### Glassmorphism (`assets/css/glassmorphism.css`)

Efek kaca buram (frosted glass) yang modern dan elegan.

#### Base Classes:
```html
<!-- Base glass effect -->
<div class="glass">Content</div>

<!-- Variants -->
<div class="glass-light">Lighter glass</div>
<div class="glass-dark">Darker glass</div>
<div class="glass-strong">Strong blur</div>
```

#### Pre-built Components:
```html
<!-- Glass Card -->
<div class="glass-card">Card content</div>

<!-- Glass Button -->
<button class="glass-btn">Click Me</button>

<!-- Glass Input -->
<input class="glass-input" placeholder="Enter text..." />

<!-- Glass Badge -->
<span class="glass-badge">NEW</span>

<!-- Glass Navigation -->
<nav class="glass-nav">Navigation</nav>

<!-- Glass with Glow -->
<div class="glass-glow">Glowing card</div>
<div class="glass-glow-blue">Blue glow</div>
<div class="glass-glow-purple">Purple glow</div>
```

#### Usage Example:
```html
<div class="glass-card hover:glass-light transition-all duration-300">
  <h3 class="text-white font-bold">Modern Card</h3>
  <p class="text-gray-300">With glassmorphism effect</p>
</div>
```

---

### Gradients (`assets/css/gradients.css`)

Berbagai kombinasi gradient untuk background, text, dan border.

#### Background Gradients:
```html
<!-- Telkom gradients -->
<div class="gradient-telkom">Telkom Red</div>
<div class="gradient-telkom-orange">Red to Orange</div>
<div class="gradient-telkom-dark">Dark Telkom</div>

<!-- Multi-color -->
<div class="gradient-rainbow">Rainbow</div>
<div class="gradient-sunset">Sunset colors</div>
<div class="gradient-ocean">Ocean blue</div>
<div class="gradient-purple-pink">Purple to Pink</div>
```

#### Text Gradients:
```html
<h1 class="text-gradient-telkom">Gradient Text</h1>
<h2 class="text-gradient-rainbow">Rainbow Text</h2>
<h3 class="text-gradient-gold">Gold Text</h3>
<p class="text-gradient-blue">Blue Text</p>
```

#### Button Gradients:
```html
<button class="btn-gradient-telkom px-6 py-3 rounded-xl">
  Telkom Button
</button>
<button class="btn-gradient-blue px-6 py-3 rounded-xl">
  Blue Button
</button>
```

#### Shadows:
```html
<div class="shadow-telkom">Red shadow</div>
<div class="shadow-telkom-lg">Large red shadow</div>
<div class="shadow-blue">Blue shadow</div>
<div class="shadow-purple">Purple shadow</div>
```

#### Mesh Gradients:
```html
<!-- Complex multi-gradient backgrounds -->
<div class="mesh-gradient-1 min-h-screen">
  Beautiful mesh gradient background
</div>
```

---

### Animations (`assets/css/animations.css`)

Animasi modern untuk interaksi yang smooth.

#### Available Animations:
```html
<!-- Float animation -->
<div class="animate-float">Floating element</div>
<div class="animate-float delay-500">Delayed float</div>

<!-- Fade in up -->
<div class="animate-fade-in-up">Fade in from bottom</div>
<div class="animate-fade-in-up delay-200">Delayed fade</div>
<div class="animate-fade-in-up delay-400">More delayed</div>

<!-- Gradient animation -->
<h1 class="text-gradient-rainbow animate-gradient">
  Animated gradient text
</h1>

<!-- Scale in -->
<div class="animate-scale-in">Scale in</div>

<!-- Slide animations -->
<div class="animate-slide-in-right">From right</div>
<div class="animate-slide-in-left">From left</div>

<!-- Glow effect -->
<button class="animate-glow text-telkom-red">Glowing button</button>

<!-- Bounce subtle -->
<div class="animate-bounce-subtle">Subtle bounce</div>

<!-- Shimmer effect -->
<div class="animate-shimmer">Shimmer effect</div>

<!-- 3D rotation -->
<div class="animate-rotate-3d">3D rotation</div>

<!-- Perspective -->
<div class="perspective-1000">
  <div class="transform hover:rotateY-12">3D card</div>
</div>
```

---

## 🧩 2. Vue Components

### GlassCard.vue

Card dengan efek glassmorphism dan berbagai varian.

#### Props:
- `variant`: 'default' | 'light' | 'dark' | 'strong'
- `glow`: boolean (default: false)
- `glowColor`: 'telkom' | 'blue' | 'purple' | 'gold'
- `hoverEffect`: boolean (default: true)
- `decorativeCorner`: boolean (default: false)
- `className`: string

#### Usage:
```vue
<template>
  <!-- Basic usage -->
  <GlassCard>
    <h3>Card Title</h3>
    <p>Card content</p>
  </GlassCard>

  <!-- With glow effect -->
  <GlassCard :glow="true" glow-color="telkom">
    <h3>Glowing Card</h3>
  </GlassCard>

  <!-- Dark variant with decorative corner -->
  <GlassCard 
    variant="dark" 
    :decorative-corner="true"
  >
    <h3>Dark Card</h3>
  </GlassCard>

  <!-- Custom class -->
  <GlassCard class-name="p-12 min-h-[300px]">
    <h3>Custom Card</h3>
  </GlassCard>
</template>
```

---

### GradientButton.vue

Button dengan gradient dan animasi hover yang smooth.

#### Props:
- `variant`: 'telkom' | 'blue' | 'purple' | 'outline' | 'glass' | 'white'
- `size`: 'sm' | 'md' | 'lg' | 'xl'
- `tag`: 'button' | 'a' | 'NuxtLink'
- `type`: 'button' | 'submit' | 'reset'
- `fullWidth`: boolean
- `showArrow`: boolean
- `animatedGradient`: boolean (default: true)
- `glow`: boolean (default: true)
- `className`: string

#### Slots:
- Default: Button text
- `icon-left`: Icon sebelum text
- `icon-right`: Icon setelah text (atau arrow jika `showArrow` true)

#### Usage:
```vue
<template>
  <!-- Basic button -->
  <GradientButton @click="handleClick">
    Click Me
  </GradientButton>

  <!-- Button with arrow -->
  <GradientButton 
    variant="telkom" 
    size="lg"
    :show-arrow="true"
  >
    Get Started
  </GradientButton>

  <!-- Blue variant with custom icon -->
  <GradientButton variant="blue">
    <template #icon-left>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </template>
    Success
  </GradientButton>

  <!-- Outline variant -->
  <GradientButton variant="outline" size="sm">
    Learn More
  </GradientButton>

  <!-- Glass variant -->
  <GradientButton variant="glass" :full-width="true">
    Full Width Button
  </GradientButton>

  <!-- As NuxtLink -->
  <GradientButton tag="NuxtLink" to="/dashboard">
    Go to Dashboard
  </GradientButton>

  <!-- Submit button -->
  <GradientButton type="submit" variant="telkom">
    Submit Form
  </GradientButton>
</template>
```

---

### FloatingIcon.vue

Icon container dengan animasi floating dan efek glow.

#### Props:
- `icon`: 'check' | 'lock' | 'star' | 'custom'
- `color`: 'telkom' | 'blue' | 'purple' | 'gold' | 'green'
- `size`: 'sm' | 'md' | 'lg'
- `glow`: boolean (default: true)
- `badge`: string (optional)
- `badgeColor`: 'yellow' | 'red' | 'blue' | 'green'
- `delay`: '0' | '500' | '1000'
- `className`: string

#### Usage:
```vue
<template>
  <!-- Pre-built icons -->
  <FloatingIcon icon="check" color="green" />
  <FloatingIcon icon="lock" color="blue" badge="Secure" />
  <FloatingIcon icon="star" color="gold" size="lg" />

  <!-- Custom icon via slot -->
  <FloatingIcon color="telkom" :glow="true">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  </FloatingIcon>

  <!-- With badge and delay -->
  <FloatingIcon 
    icon="check" 
    color="blue"
    badge="New"
    badge-color="yellow"
    delay="500"
  />

  <!-- Different sizes -->
  <FloatingIcon icon="lock" color="purple" size="sm" />
  <FloatingIcon icon="star" color="gold" size="md" />
  <FloatingIcon icon="check" color="green" size="lg" />
</template>
```

---

## 🎯 3. Pattern Examples

### Hero Section Pattern
```vue
<template>
  <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 mesh-gradient-1"></div>
    
    <!-- Floating Decorations -->
    <div class="absolute top-20 left-20 w-96 h-96 bg-telkom-red/30 rounded-full filter blur-3xl animate-pulse"></div>
    <div class="absolute bottom-20 right-20 w-96 h-96 bg-blue-600/30 rounded-full filter blur-3xl animate-pulse delay-1000"></div>

    <!-- Content -->
    <div class="container mx-auto px-4 relative z-10">
      <div class="text-center animate-fade-in-up">
        <h1 class="text-7xl font-black mb-6">
          <span class="text-gradient-rainbow animate-gradient">
            Modern Design
          </span>
        </h1>
        <p class="text-xl text-gray-300 mb-8">
          Beautiful glassmorphism and gradients
        </p>
        <GradientButton size="lg" :show-arrow="true">
          Get Started
        </GradientButton>
      </div>
    </div>
  </section>
</template>
```

### Feature Card Pattern
```vue
<template>
  <div class="grid md:grid-cols-3 gap-8">
    <GlassCard 
      v-for="feature in features" 
      :key="feature.id"
      :glow="true"
      :glow-color="feature.color"
      class="group"
    >
      <!-- Icon -->
      <FloatingIcon 
        :icon="feature.icon"
        :color="feature.color"
        :badge="feature.badge"
        class-name="mb-6"
      />

      <!-- Content -->
      <h3 class="text-2xl font-bold text-white mb-4">
        {{ feature.title }}
      </h3>
      <p class="text-gray-400">
        {{ feature.description }}
      </p>

      <!-- Arrow indicator -->
      <div class="absolute bottom-8 right-8 w-10 h-10 bg-telkom-red/10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
        <svg class="w-5 h-5 text-telkom-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
      </div>
    </GlassCard>
  </div>
</template>
```

### Navigation Pattern
```vue
<template>
  <nav class="glass-nav fixed top-0 left-0 right-0 z-50">
    <div class="container mx-auto px-4">
      <div class="flex items-center justify-between py-4">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 gradient-telkom rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-xl">T</span>
          </div>
          <span class="text-white font-bold text-lg">Your Brand</span>
        </div>

        <!-- Menu -->
        <div class="flex gap-6">
          <a href="#" class="text-white hover:text-yellow-400 transition">Home</a>
          <a href="#" class="text-white hover:text-yellow-400 transition">Features</a>
          <a href="#" class="text-white hover:text-yellow-400 transition">About</a>
        </div>

        <!-- CTA -->
        <GradientButton variant="white" size="sm">
          Login
        </GradientButton>
      </div>
    </div>
  </nav>
</template>
```

---

## 🚀 Quick Start

### 1. Import CSS files (sudah ditambahkan di `main.css`):
```css
@import './glassmorphism.css';
@import './gradients.css';
@import './animations.css';
```

### 2. Gunakan komponennya:
```vue
<script setup lang="ts">
// Components auto-imported by Nuxt
</script>

<template>
  <div>
    <GlassCard :glow="true">
      <h2>Hello World</h2>
    </GlassCard>
    
    <GradientButton variant="telkom" :show-arrow="true">
      Click Me
    </GradientButton>
    
    <FloatingIcon icon="star" color="gold" />
  </div>
</template>
```

---

## 🎨 Color Palette (dari Telkom)

```css
/* Primary */
--telkom-red: #EF3124
--telkom-red-dark: #DC2316
--telkom-grey: #6D6E71
--telkom-blue: #0071BC

/* Gradients */
gradient-telkom: #EF3124 → #DC2316
gradient-telkom-orange: #EF3124 → #F17641
gradient-rainbow: #EF3124 → #9333ea → #0071BC
```

---

## 📝 Best Practices

1. **Glassmorphism**: Gunakan pada elemen yang ingin terlihat "mengambang" di atas background
2. **Gradients**: Kombinasikan dengan animasi untuk efek yang lebih dinamis
3. **Animations**: Jangan overuse - pilih 2-3 animasi utama per halaman
4. **Components**: Prefer menggunakan Vue components daripada menulis HTML berulang
5. **Consistency**: Gunakan variant dan size yang konsisten di seluruh aplikasi

---

## 🔧 Customization

### Membuat Custom Variant:

#### Di glassmorphism.css:
```css
.glass-custom {
  background: rgba(255, 0, 255, 0.1);
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 0, 255, 0.2);
}
```

#### Di gradients.css:
```css
.gradient-custom {
  background: linear-gradient(135deg, #your-color-1, #your-color-2);
}
```

#### Di animations.css:
```css
@keyframes custom-animation {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.animate-custom {
  animation: custom-animation 2s ease-in-out infinite;
}
```

---

## 📦 File Structure

```
frontend/
├── assets/
│   └── css/
│       ├── main.css              # Import semua CSS
│       ├── glassmorphism.css     # Glass effects
│       ├── gradients.css         # Gradient utilities
│       └── animations.css        # Animation keyframes
├── components/
│   ├── GlassCard.vue            # Glass card component
│   ├── GradientButton.vue       # Gradient button component
│   └── FloatingIcon.vue         # Floating icon component
└── pages/
    └── index.vue                # Landing page example
```

---

## 🎓 Tips & Tricks

### Combine Multiple Effects:
```html
<GlassCard 
  :glow="true" 
  glow-color="telkom"
  class="animate-fade-in-up delay-200"
>
  <FloatingIcon 
    icon="star" 
    color="gold"
    badge="New"
  />
  <h3 class="text-gradient-rainbow animate-gradient">
    Amazing Feature
  </h3>
  <GradientButton 
    variant="telkom"
    :show-arrow="true"
    class="mt-4"
  >
    Learn More
  </GradientButton>
</GlassCard>
```

### Responsive Design:
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
  <GlassCard 
    v-for="item in items"
    :key="item.id"
    class="animate-fade-in-up"
    :style="{ animationDelay: `${item.id * 0.1}s` }"
  >
    <!-- Content -->
  </GlassCard>
</div>
```

---

Dengan design system ini, Anda bisa membuat halaman yang konsisten, modern, dan futuristik di seluruh project! 🚀✨
