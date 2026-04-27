<template>
  <div class="add-item-page">
    <header class="admin-header">
      <h1 class="admin-title">Dodaj przedmiot</h1>
      <p class="admin-subtitle">Uzupełnij dane przedmiotu do ruletki</p>
    </header>

    <form class="item-form" @submit.prevent="submit">
      <div class="form-row">
        <label>Nazwa</label>
        <input v-model="name" placeholder="Np. C4" />
      </div>

      <div class="form-row">
        <label>Szansa %</label>
        <input v-model="chance" type="number" step="0.01" placeholder="Np. 10" />
      </div>

      <div class="form-row">
        <label>Ikona</label>
        <input type="file" @change="onFileChange" />
      </div>

      <div class="form-row">
        <label>Rzadkość</label>
        <select v-model="rarity">
          <option value="common">Zwykły</option>
          <option value="uncommon">Niezwykły</option>
          <option value="rare">Rzadki</option>
          <option value="epic">Epicki</option>
          <option value="legendary">Legendarny</option>
        </select>
      </div>

      <div class="form-row">
        <label>Ilość</label>
        <input v-model="count" type="number" min="1" placeholder="Ilość" />
      </div>

      <div class="form-row">
        <label>ID przedmiotu w grze</label>
        <input v-model="gameitemid" type="number" placeholder="Game item ID" />
      </div>

      <div class="form-actions">
        <button class="admin-add-button" type="submit" :disabled="saving">
          {{ saving ? 'Dodawanie...' : '➕ Dodaj przedmiot' }}
        </button>

        <button class="modal-secondary" type="button" @click="$router.push('/acp')">
          ⬅ Wróć
        </button>
      </div>
    </form>
  </div>

  <!-- POPWINDOW-->
  <div v-if="resultModal" class="modal-backdrop">
    <div :class="['result-modal', resultModal.type]">
      <button class="modal-close" @click="closeResultModal">×</button>

      <h2>
        {{ resultModal.type === 'success' ? '✅ Sukces' : '⚠️ Błąd' }}
      </h2>

      <p>{{ resultModal.message }}</p>

      <button class="modal-ok" @click="closeResultModal">
        OK
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '../config/api'
import { useItems } from '../composables/useItems'

const router = useRouter()
const { resetItems } = useItems()

const name = ref('')
const chance = ref(0)
const file = ref(null)
const rarity = ref('common')
const count = ref(1)
const gameitemid = ref(0)

const saving = ref(false)
const resultModal = ref(null)

function onFileChange(e) {
  file.value = e.target.files[0]
}

function showResult(type, message) {
  resultModal.value = { type, message }
}

function closeResultModal() {
  const wasSuccess = resultModal.value?.type === 'success'

  resultModal.value = null

  if (wasSuccess) {
    router.push('/acp')
  }
}

async function submit() {
  if (saving.value) return

  saving.value = true

  const formData = new FormData()

  formData.append('name', name.value)
  formData.append('chance', chance.value)
  formData.append('rarity', rarity.value)
  formData.append('count', count.value)
  formData.append('game_item_id', gameitemid.value)

  if (file.value) {
    formData.append('icon', file.value)
  }

  try {
    const res = await fetch(`${API_URL}/api/admin/add-item`, {
      method: 'POST',
      body: formData,
      credentials: 'include'
    })

    const data = await res.json().catch(() => ({}))

    if (!res.ok) {
      showResult('error', data.message ?? data.error ?? 'Nie można dodać itemu.')
      return
    }

    resetItems()

    showResult('success', 'Przedmiot został dodany pomyślnie.')
  } catch (e) {
    showResult('error', 'Brak połączenia z serwerem.')
  } finally {
    saving.value = false
  }
}
</script>