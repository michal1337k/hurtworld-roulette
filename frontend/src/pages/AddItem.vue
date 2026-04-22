<template>
  <div>
    <h1>Dodaj item</h1>
    <form @submit.prevent="submit">
      Nazwa: <input v-model="name" placeholder="Nazwa" />
      Szansa %:<input v-model="chance" type="number" placeholder="Szansa %" />
      Ikona:<input type="file" @change="onFileChange" />
      Rzadkość:
      <select v-model="rarity">
        <option value="common">Common</option>
        <option value="uncommon">Uncommon</option>
        <option value="rare">Rare</option>
        <option value="epic">Epic</option>
        <option value="legendary">Legendary</option>
      </select>

      Ilość:
      <input v-model="count" type="number" placeholder="Ilość" />
      
      Id przedmiotu w grze:
      <input v-model="gameitemid" placeholder="Id przedmiotu w grze" />
      <button>Dodaj</button>
    </form>

    <button @click="$router.push('/acp')">
      ⬅ wróć
    </button>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { API_URL } from '../config/api'

const name = ref('')
const chance = ref(0)
const file = ref(null)
const rarity = ref('common')
const count = ref(1)
const gameitemid = ref(0)


function onFileChange(e) {
  file.value = e.target.files[0]
}

async function submit() {
  const formData = new FormData()

  formData.append('name', name.value)
  formData.append('chance', chance.value)
  formData.append('rarity', rarity.value)
  formData.append('count', count.value)
  formData.append('game_item_id', gameitemid.value)
  
  if (file.value) {
    formData.append('icon', file.value)
  }

  const res = await fetch(`${API_URL}/api/admin/add-item`, {
    method: 'POST',
    body: formData,
    credentials: 'include'
  })

  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    alert(err.message ?? 'Nie można dodać itemu (limit 100%)')
    return
  }
  
  name.value = ''
  chance.value = 0
  file.value = null
  rarity.value = 'common'
  count.value = 1
  gameitemid.value = 0

  alert('Dodano item')
}
</script>