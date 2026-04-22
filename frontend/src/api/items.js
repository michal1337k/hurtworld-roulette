import { API_URL } from '../config/api'

export async function fetchItems() {
  const res = await fetch(`${API_URL}/api/admin/items`, {
    credentials: 'include'
  })

  if (!res.ok) {
    throw new Error('Błąd pobierania itemów')
  }

  return await res.json()
}