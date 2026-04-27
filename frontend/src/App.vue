<script setup>
import { auth } from './store/auth'
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
</script>

<template>
  <div v-if="!auth.loaded" class="app-loading">
    Loading...
  </div>

  <div v-else class="app-shell">
    <Navbar />
    <main class="app-main">
      <router-view />
    </main>
    <Footer />
  </div>
</template>

<style>
.app-loading {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}
</style>