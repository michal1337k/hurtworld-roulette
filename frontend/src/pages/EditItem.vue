<template>
  <div>
    <h2>✏️ Edytuj item</h2>

    <input v-model="item.name" placeholder="Nazwa" />
    <input v-model="item.chance" type="number" />

    <div class="icon-preview">
        <img v-if="previewUrl" :src="previewUrl" />
        <img v-else-if="item.icon" :src="getIcon(item.icon)" />
    </div>

    <input type="file" @change="onFileChange" />

    <button @click="save">Zapisz</button>

    <button @click="$router.push('/acp')">
      ⬅ wróć
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const file = ref(null)
const previewUrl = ref(null)

const item = ref({})

function onFileChange(e) {
  file.value = e.target.files[0]
    if (file) {
        previewUrl.value = URL.createObjectURL(file)
    }
}

function getIcon(path) {
  return `http://localhost:8080${path}`
}

async function loadItem() {
  const res = await fetch('http://localhost:8080/api/admin/items', {
    credentials: 'include'
    })
  const data = await res.json()

  item.value = data.find(i => i.id == route.params.id)
}

async function save() {
  const formData = new FormData()

  formData.append('name', item.value.name)
  formData.append('chance', item.value.chance)

  if (file.value) {
    formData.append('icon', file.value)
  }

  await fetch(`http://localhost:8080/api/admin/item/${route.params.id}`, {
    method: 'PUT',
    body: formData,
    credentials: 'include'
  })

  router.push('/acp')
}

onMounted(loadItem)
</script>
<style>
.icon-preview {
  width: 60px;
  height: 60px;
  border: 1px solid #757575;
  border-radius: 8px;

  display: flex;
  align-items: center;
  justify-content: center;

  background: #47454536;
  overflow: hidden;
  margin: 10px 0;
}

.icon-preview img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
</style>