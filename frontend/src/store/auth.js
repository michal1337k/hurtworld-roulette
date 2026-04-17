import { reactive } from 'vue'
import { API_URL } from '../config/api'

export const auth = reactive({
  user: null,
  loaded: false
})

export async function fetchUser() {
  const res = await fetch(`${API_URL}/api/me`, {
    credentials: 'include'
  })

  if (res.ok) {
    auth.user = await res.json()
  }
}