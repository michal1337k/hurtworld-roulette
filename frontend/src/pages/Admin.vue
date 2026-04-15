<template>
  <div>
    <h1>Admin Panel</h1>

    <p v-if="items.length === 0">
      brak przedmiotów w ruletce
    </p>

    <div v-else>
        <div class="p-6">

            <h2>🎰 Items (Roulette)</h2>
            <button @click="$router.push('/acp/add-item')">
                ➕ Dodaj item
            </button>
            <table class="items-table">
            <thead>
                <tr>
                <th>Ikona</th>
                <th>Nazwa</th>
                <th>Szansa %</th>
                <th>Akcje</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="item in items" :key="item.id">
                <td>
                    <div class="icon-wrapper">
                        <img :src="getIcon(item.icon)" />
                    </div>
                </td>

                <td>{{ item.name }}</td>

                <td>{{ item.chance }}%</td>

                <td>
                    <button @click="editItem(item)">
                    ✏️ Edytuj
                    </button>
                </td>
                </tr>
            </tbody>
            </table>

        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const items = ref([])

async function loadItems() {
  const res = await fetch('http://localhost:8080/api/admin/items', {
    credentials: 'include'
  })

  items.value = await res.json()
}

function editItem(item) {
  router.push(`/acp/edit-item/${item.id}`)
}

onMounted(() => {
  loadItems()
})

function getIcon(path) {
  return `http://localhost:8080${path}`
}
</script>
<style scoped>
.items-table {
  width: 100%;
  margin-top: 20px;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
}

.items-table thead tr {
  border-bottom: 2px solid #ddd;
}

.items-table th,
.items-table td {
  padding: 12px 10px;
  text-align: left;
}

.items-table tbody tr:hover {
  background: #221536;
}

.icon-wrapper {
  width: 60px;
  height: 60px;
  border: 1px solid #757575;
  border-radius: 8px;

  display: flex;
  align-items: center;
  justify-content: center;

  background: #47454536;
  overflow: hidden;
}

.icon-wrapper img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>