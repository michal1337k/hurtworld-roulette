import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { auth } from './store/auth'
import { API_URL } from './config/api'
import './style.css'

const app = createApp(App)

async function init() {
  try {
    const res = await fetch(`${API_URL}/api/me`, {
      credentials: 'include'
    })

    if (res.ok) {
      auth.user = await res.json()
    }
  } catch (e) {
    console.log('not logged in')
  }

  auth.loaded = true

  app.use(router)
  app.mount('#app')
}

init()