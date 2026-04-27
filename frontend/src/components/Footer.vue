<template>
  <footer class="app-footer">
    <span>© {{ year }} Hurtworld Roulette</span>
    <span>
      Author:
      <a href="https://github.com/michal1337k" target="_blank" rel="noopener noreferrer">
        github.com/michal1337k
      </a>
    </span>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>