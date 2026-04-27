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
            {{ formatBalance(auth.user.balance) }}
          </div>
        </div>

        <router-link to="/inventory" class="icon-btn">
          📦
        </router-link>

        <button class="icon-btn logout" @click="logout">
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
  if (value === null || value === undefined) return '0,00 zł'

  return new Intl.NumberFormat('pl-PL', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value / 100) + ' zł'
}

</script>

<style>
/* ================= NAVBAR ================= */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 14px 24px;
  background: rgba(20, 20, 25, 0.8);
  backdrop-filter: blur(10px);

  border-bottom: 1px solid #333;
  color: #fff;
}

/* LEFT */
.left {
  display: flex;
  gap: 20px;
  align-items: center;
}

/* LINKI */
.navbar a {
  color: white;
  text-decoration: none;
  opacity: 0.8;
  transition: 0.2s;
  font-weight: 500;
}

.navbar a:hover {
  opacity: 1;
  transform: translateY(-1px);
}

/* RIGHT */
.right {
  display: flex;
  align-items: center;
}

/* USER */
.user {
  display: flex;
  align-items: center;
  gap: 14px;
}

/* AVATAR */
.avatar {
  width: 48px;   /* większy */
  height: 48px;
  border-radius: 50%;
  border: 2px solid #555;
  transition: 0.2s;
}

.avatar:hover {
  border-color: #888;
}

/* INFO */
.info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

/* NAME */
.name {
  font-weight: 600;
  font-size: 14px;
}

/* BALANCE */
.balance {
  font-size: 13px;
  color: #4caf50;        /* zielony */
  font-weight: 700;      /* pogrubienie */
}

/* IKONKI (inventory + logout) */
.icon-btn {
  display: flex;
  align-items: center;
  justify-content: center;

  width: 36px;
  height: 36px;

  border-radius: 8px;
  border: 1px solid #444;
  background: transparent;
  color: white;

  cursor: pointer;
  transition: 0.2s;
}

.icon-btn:hover {
  background: #222;
  transform: scale(1.05);
}

/* LOGOUT specjalny */
.logout:hover {
  background: #3a1f1f;
  border-color: #aa4444;
}

/* LOGIN */
.login {
  padding: 8px 14px;
  border: 1px solid #555;
  border-radius: 8px;
  transition: 0.2s;
}

.login:hover {
  background: #222;
}
</style>