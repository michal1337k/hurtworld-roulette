<template>
  <div>
    <h1>Admin Panel</h1>

    <h2>🎰 Items (Roulette)</h2>
    <button @click="$router.push('/acp/add-item')">
        ➕ Dodaj item
    </button>

    <!-- LOADING -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Ładowanie...</p>
    </div>

    <!-- BRAK ITEMÓW -->
    <p v-else-if="items.length === 0">
      brak przedmiotów w ruletce
    </p>

    <div v-else>
        <div class="p-6">
            <table class="items-table">
            <thead>
                <tr>
                <th>Ikona</th>
                <th @click="sortBy('name')" class="sortable">
                  Nazwa
                  <span v-if="sortKey === 'name'">
                    {{ sortDir === 'asc' ? '▲' : '▼' }}
                  </span>
                </th>

                <th @click="sortBy('chance')" class="sortable">
                  Szansa %
                  <span v-if="sortKey === 'chance'">
                    {{ sortDir === 'asc' ? '▲' : '▼' }}
                  </span>
                </th>
                <th @click="sortBy('count')" class="sortable">
                  Ilość
                  <span v-if="sortKey === 'count'">
                    {{ sortDir === 'asc' ? '▲' : '▼' }}
                  </span>
                </th>

                <th @click="sortBy('rarity')" class="sortable">
                  Rzadkość
                  <span v-if="sortKey === 'rarity'">
                    {{ sortDir === 'asc' ? '▲' : '▼' }}
                  </span>
                </th>
                <th>Akcje</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="item in sortedItems" :key="item.id">
                  <!-- IKONA -->
                <td>
                  <div
                    class="icon-wrapper"
                    :class="`rarity-${item.rarity || 'common'}`"
                  >
                    <img :src="getIcon(item.icon)" />
                  </div>
                  <input
                    v-if="editingId === item.id"
                    type="file"
                    @change="e => onFileChange(e, item.id)"
                  />
                </td>

                  <!-- NAZWA -->
                  <td>
                    <span v-if="editingId !== item.id">
                      {{ item.name }}
                    </span>

                    <input v-else v-model="editedItem.name" />
                  </td>

                  <!-- CHANCE -->
                  <td>
                    <span v-if="editingId !== item.id">
                      {{ item.chance }}%
                    </span>

                    <input v-else type="number" v-model="editedItem.chance" />
                  </td>

                  <!-- COUNT -->
                  <td>
                    <span v-if="editingId !== item.id">
                      {{ item.count }}
                    </span>

                    <input v-else type="number" v-model="editedItem.count" />
                  </td>

                  <!-- RARITY -->
                  <td>
                    <span v-if="editingId !== item.id">
                      {{ item.rarity }}
                    </span>

                    <select v-else v-model="editedItem.rarity">
                      <option value="common">Zwykły</option>
                      <option value="uncommon">Niezwykły</option>
                      <option value="rare">Rzadki</option>
                      <option value="epic">Epicki</option>
                      <option value="legendary">Legendarny</option>
                    </select>
                  </td>
                  <!-- AKCJE -->
                  <td>
                    <button v-if="editingId !== item.id" @click="startEdit(item)">
                      ✏️
                    </button>
                    <button v-if="editingId !== item.id" @click="confirmDelete(item)">
                      🗑️
                    </button>
                    <template v-else>
                      <button @click="saveEdit(item.id)">
                        💾
                      </button>
                      <button @click="cancelEdit">
                        ❌
                      </button>
                    </template>
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
import { API_URL } from '../config/api'
import { computed } from 'vue'

const router = useRouter()
const items = ref([])
const loading = ref(true)
const editingId = ref(null)
const editedItem = ref({})

const sortKey = ref(null)
const sortDir = ref('asc')


async function loadItems() {
  loading.value = true

  const res = await fetch(`${API_URL}/api/admin/items`, {
    credentials: 'include'
  })

  items.value = await res.json()

  loading.value = false
}

function editItem(item) {
  router.push(`/acp/edit-item/${item.id}`)
}

onMounted(() => {
  loadItems()
})

function getIcon(path) {
  return `${API_URL}${path}`
}

function onFileChange(e, id) {
  editedItem.value.file = e.target.files[0]
}

function startEdit(item) {
  editingId.value = item.id
  editedItem.value = { ...item }
}

async function saveEdit(id) {
  const formData = new FormData()

  formData.append('name', editedItem.value.name)
  formData.append('chance', editedItem.value.chance)
  formData.append('count', editedItem.value.count)
  formData.append('rarity', editedItem.value.rarity)

  if (editedItem.value.file) {
    formData.append('icon', editedItem.value.file)
  }

  const res = await fetch(`${API_URL}/api/admin/edit-item/${id}`, {
    method: 'POST',
    body: formData,
    credentials: 'include'
  })

  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    alert(err.message ?? 'Błąd edycji (limit 100%)')
    return
  }

  editingId.value = null
  loadItems()
}

function sortBy(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDir.value = 'asc'
  }
}

const sortedItems = computed(() => {
  const arr = [...items.value]

  if (!sortKey.value) return arr

  return arr.sort((a, b) => {
    let valA = a[sortKey.value]
    let valB = b[sortKey.value]

    if (sortKey.value === 'chance' || sortKey.value === 'count') {
      valA = Number(valA)
      valB = Number(valB)
    }

    if (sortKey.value === 'rarity') {
      const order = {
        common: 1,
        uncommon: 2,
        rare: 3,
        epic: 4,
        legendary: 5
      }

      valA = order[valA] ?? 0
      valB = order[valB] ?? 0
    }

    if (valA < valB) return sortDir.value === 'asc' ? -1 : 1
    if (valA > valB) return sortDir.value === 'asc' ? 1 : -1
    return 0
  })
})

async function confirmDelete(item) {
  const ok = confirm(`Na pewno usunąć item: ${item.name}?`)

  if (!ok) return

  const res = await fetch(`${API_URL}/api/admin/delete-item/${item.id}`, {
    method: 'DELETE',
    credentials: 'include'
  })

  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    alert(err.message ?? 'Błąd usuwania')
    return
  }

  loadItems()
}

function cancelEdit() {
  editingId.value = null
  editedItem.value = {}
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

.loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 50px;
  gap: 10px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #444;
  border-top: 4px solid #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.sortable {
  cursor: pointer;
  user-select: none;
}

.sortable:hover {
  background: rgba(255, 255, 255, 0.05);
}

.icon-wrapper {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  background: #47454536;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  border: 2px solid #757575; 
}

.icon-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.icon-wrapper.rarity-common {
  border-color: #9e9e9e;
}

.icon-wrapper.rarity-uncommon {
  border-color: #4caf50;
}

.icon-wrapper.rarity-rare {
  border-color: #2196f3;
}

.icon-wrapper.rarity-epic {
  border-color: #9c27b0;
  box-shadow: 0 0 6px #9c27b0;
}

.icon-wrapper.rarity-legendary {
  border-color: #ffc107;
  box-shadow: 0 0 10px #ffc107;
}
</style>