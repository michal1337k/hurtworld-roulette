<template>
<div class="admin-page">
  <header class="admin-header">
    <h1 class="admin-title">Admin Panel</h1>
    <p class="admin-subtitle">Lista przedmiotów w bazie ruletki</p>

    <button class="admin-add-button" @click="$router.push('/acp/add-item')">
      ➕ Dodaj przedmiot
    </button>
    <br>
    <div class="chance-summary">
      <div>
        Łączna szansa dropu:
        <strong :class="{ danger: totalChance > 100 }">
          {{ totalChance.toFixed(2) }}%
        </strong>
      </div>

      <div>
        Pozostało:
        <strong>
          {{ chanceLeft.toFixed(2) }}%
        </strong>
      </div>
    </div>

  </header>

    <!-- LOADING -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Ładowanie...</p>
    </div>

    <!-- BRAK ITEMÓW -->
    <p v-else-if="items.length === 0">
      brak przedmiotów w ruletce
    </p>

  <div v-else class="admin-table-wrapper">
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
          <th @click="sortBy('game_item_id')" class="sortable">
            Id przedmiotu w grze
            <span v-if="sortKey === 'game_item_id'">
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

            <!-- GAME ITEM ID -->
            <td>
              <span v-if="editingId !== item.id">
                {{ item.game_item_id }}
              </span>

              <input v-else v-model="editedItem.game_item_id" />
            </td>
            <!-- AKCJE -->
            <td class="actions-cell">
              <template v-if="editingId !== item.id">
                <button class="table-action edit" @click="startEdit(item)">
                  ✏️
                </button>

                <button class="table-action delete" @click="openDeleteModal(item)">
                  ⛔
                </button>
              </template>

              <template v-else>
                <button class="table-action save" @click="saveEdit(item.id)">
                  💾
                </button>

                <button class="table-action cancel" @click="cancelEdit">
                  ✖
                </button>
              </template>
            </td>
          </tr>
      </tbody>
    </table>

          
  </div>
</div>

<!-- POPUP WINDOW -->
<div v-if="deleteModal" class="modal-backdrop">
  <div class="confirm-modal">
    <button class="modal-close" @click="closeDeleteModal">×</button>

    <h2>Potwierdzenie</h2>

    <p>
      Czy na pewno usunąć przedmiot:
      <strong>{{ deleteModal.name }}</strong>?
    </p>

    <div class="modal-actions">
      <button class="modal-ok danger" @click="deleteItem">
        Tak, usuń
      </button>

      <button class="modal-secondary" @click="closeDeleteModal">
        Nie
      </button>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '../config/api'
import { useItems } from '../composables/useItems'


const router = useRouter()

const editingId = ref(null)
const editedItem = ref({})
const { items, loadItems, resetItems, loading } = useItems()
const sortKey = ref(null)
const sortDir = ref('asc')
const deleteModal = ref(null)

const totalChance = computed(() => {
  return items.value.reduce((sum, item) => {
    return sum + Number(item.chance)
  }, 0)
})

const chanceLeft = computed(() => {
  return Math.max(0, 100 - totalChance.value)
})

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
  formData.append('game_item_id', editedItem.value.game_item_id)
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
  resetItems()
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

function openDeleteModal(item) {
  deleteModal.value = item
}

function closeDeleteModal() {
  deleteModal.value = null
}

async function deleteItem() {
  if (!deleteModal.value) return

  const res = await fetch(`${API_URL}/api/admin/delete-item/${deleteModal.value.id}`, {
    method: 'DELETE',
    credentials: 'include'
  })

  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    alert(err.message ?? 'Błąd usuwania')
    return
  }

  closeDeleteModal()
  resetItems()
  loadItems()
}

function cancelEdit() {
  editingId.value = null
  editedItem.value = {}
}
</script>
