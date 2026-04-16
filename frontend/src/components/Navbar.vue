<template>
  <nav class="navbar">
    
    <!-- LEFT -->
    <div class="left">
        <a href="/">🏠 Strona główna</a>

        <router-link v-if="auth.user?.roles?.includes('ROLE_ADMIN')" to="/acp">
            ⚙️ ACP
        </router-link>
    </div>

    <!-- RIGHT -->
    <div class="right">

      <div v-if="auth.user" class="user">
        <img :src="auth.user.avatar" class="avatar" />

        <div class="info">
          <div class="name">
            {{ formatName(auth.user.nickname) }}
          </div>

          <div class="balance">
            💰 {{ formatBalance(auth.user.balance) }}
          </div>
        </div>

        <button> <!-- inventory todo -->
            🎒
        </button>
        <button class="logout" @click="logout">
          ⏻
        </button>
        
      </div>

      <div v-else>
        <a class="login" :href="steamLoginUrl">
          Zaloguj przez Steam
        </a>
      </div>

    </div>

  </nav>
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

function logout() {
  window.location.href = `${API_URL}/logout`
}

function formatBalance(value) {
  if (value === null || value === undefined) return '0,00$'

  return (value / 100)
    .toFixed(2)
    .replace('.', ',') + '$'
}

</script>

<style>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 12px 20px;
  background: #1f1f1f7c;
  border-bottom: 1px solid #333;
  color: #fff;
  font-family: Arial, sans-serif;
}

/* LEFT SIDE */
.left {
  display: flex;
  gap: 16px;
  align-items: center;
}

/* RIGHT SIDE */
.right {
  display: flex;
  align-items: center;
}

/* LINKS */
.navbar a {
  color: white;
  text-decoration: none;
  opacity: 0.8;
  transition: 0.2s;
}

.navbar a:hover {
  opacity: 1;
}

/* USER BLOCK */
.user {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* AVATAR */
.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #444;
}

/* NAME + BALANCE */
.info {
  display: flex;
  flex-direction: column;
  font-size: 12px;
}

.name {
  font-weight: bold;
}

.balance {
  opacity: 0.8;
}

/* LOGOUT BUTTON */
.logout {
  background: transparent;
  border: 1px solid #444;
  color: white;
  padding: 4px 8px;
  border-radius: 6px;
  cursor: pointer;
}

.logout:hover {
  background: #222;
}

/* LOGIN */
.login {
  padding: 6px 10px;
  border: 1px solid #555;
  border-radius: 6px;
}
</style>