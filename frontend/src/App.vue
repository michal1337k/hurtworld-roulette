<script setup>
import { auth } from './store/auth'
import Navbar from './components/Navbar.vue'
</script>

<template>
  <div v-if="!auth.loaded" class="app-loading">
    Loading...
  </div>

  <div v-else>
    <Navbar />
    <router-view />
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