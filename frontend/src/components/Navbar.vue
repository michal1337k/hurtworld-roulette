<template>
  <div class="navbar">
    <a href="/">🏠 Home</a>
    <a href="/acp">⚙️ ACP</a>

    <div v-if="auth.user">
    <img :src="auth.user.avatar" width="60" />

    <h3>{{ formatName(auth.user.nickname) }}</h3>

    <p>💰 Stan konta: {{ formatBalance(auth.user.balance) }}</p>

    <button @click="logout">
        🚪 Wyloguj
    </button>
    </div>

    <div v-else>
    <a :href="steamLoginUrl">Zaloguj za pomocą Steama</a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { API_URL } from '../config/api'
import { auth } from '../store/auth'

const steamLoginUrl = `${API_URL}/login/steam`

function formatName(name) {
  return name.length > 10
    ? name.slice(0, 7) + '...'
    : name
}

async function logout() {
  await fetch(`${API_URL}/logout`, {
    credentials: 'include'
  })

  user.value = null
}

function formatBalance(value) {
  if (value === null || value === undefined) return '0,00$'

  return (value / 100)
    .toFixed(2)
    .replace('.', ',') + '$'
}

</script>
