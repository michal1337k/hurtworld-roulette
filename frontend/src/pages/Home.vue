<template>
<div style="padding: 20px">

    <h1>🎰 Ruletka Hurtworld Legacy</h1>

    <div v-if="user">
      <img :src="user.avatar" width="60" />

      <h3>{{ formatName(user.nickname) }}</h3>

      <p>💰 Stan konta: {{ formatBalance(user.balance) }}</p>

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


const user = ref(null)
const steamLoginUrl = 'http://localhost:8080/login/steam'

function formatName(name) {
  return name.length > 10
    ? name.slice(0, 7) + '...'
    : name
}

async function loadMe() {
  try {
    const res = await fetch('http://localhost:8080/api/me', {
      credentials: 'include'
    })

    user.value = await res.json()
  } catch (e) {
    console.log('nie zalogowany')
  }
}

async function logout() {
  await fetch('http://localhost:8080/logout', {
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

onMounted(() => {
  loadMe()
})
</script>