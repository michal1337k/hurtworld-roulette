import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { auth } from './store/auth'
import './style.css'

async function loadUser() {
  const res = await fetch('http://localhost:8080/api/me', {
    credentials: 'include'
  })

  if (res.ok) {
    auth.user = await res.json()
  }

  auth.loaded = true
}

loadUser().then(() => {
  createApp(App)
    .use(router)
    .mount('#app')
})