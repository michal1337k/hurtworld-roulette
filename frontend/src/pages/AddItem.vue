<template>
  <div>
    <h1>Dodaj item</h1>
    <form @submit.prevent="submit">
      Nazwa: <input v-model="name" placeholder="Nazwa" />
      Szansa %:<input v-model="chance" type="number" placeholder="Szansa %" />
      Ikona:<input type="file" @change="onFileChange" />

      <button>Dodaj</button>
    </form>

    <button @click="$router.push('/acp')">
      ⬅ wróć
    </button>
  </div>
</template>
<script setup>
import { ref } from 'vue'

const name = ref('')
const chance = ref(0)
const file = ref(null)

function onFileChange(e) {
  file.value = e.target.files[0]
}

async function submit() {
  const formData = new FormData()

  formData.append('name', name.value)
  formData.append('chance', chance.value)
  formData.append('icon', file.value)
  
  await fetch('http://localhost:8080/api/admin/add-item', {
    method: 'POST',
    body: formData,
    credentials: 'include'
  })
}
</script>