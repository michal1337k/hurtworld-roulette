<template>
  <div class="inventory-page">
    <header class="inventory-header">
      <h1 class="admin-title">Ekwipunek</h1>
      <p class="admin-subtitle">Twoje wygrane przedmioty</p>
    </header>

    <section class="inventory-info">
      <p>Przedmioty można odebrać w grze za pomocą komendy:</p>

      <ul>
        <li>
          <code>/ruletka odbierz all</code>
          <span>odbiera wszystkie przedmioty, maksymalnie 5 na raz</span>
        </li>

        <li>
          <code>/ruletka odbierz &lt;nazwa&gt; &lt;ilosc&gt;</code>
          <span>odbiera określony przedmiot po nazwie oraz określoną ilość</span>
        </li>
      </ul>

      <p class="inventory-hint">
        Nazwę przedmiotu zobaczysz po najechaniu na ikonę w ekwipunku.
      </p>
    </section>

    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Ładowanie ekwipunku...</p>
    </div>

    <div v-else class="inventory-wrapper">
      <div class="inventory">
        <div
          v-for="(slot, index) in slots"
          :key="index"
          class="slot"
        >
          <div
            v-if="slot"
            class="item inventory-item"
            :class="`rarity-${slot.rarity}`"
          >
            <div class="item-tooltip">
              {{ slot.name }}
            </div>

            <img :src="getIcon(slot.icon)" />

            <div class="count">
              x{{ slot.count }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '../config/api'

const inventory = ref([])
const loading = ref(true)
const rarityOrder = {
  legendary: 5,
  epic: 4,
  rare: 3,
  uncommon: 2,
  common: 1
}
const slots = computed(() => {
  const filled = [...inventory.value]

  const minSlots = 10

  while (filled.length < minSlots) {
    filled.push(null)
  }

  return filled
})

async function loadInventory() {
  loading.value = true

  try {
    const res = await fetch(`${API_URL}/api/inventory`, {
      credentials: 'include'
    })

    const data = await res.json()

    inventory.value = data.sort(
      (a, b) => rarityOrder[b.rarity] - rarityOrder[a.rarity]
    )
  } finally {
    loading.value = false
  }
}

function getIcon(path) {
  return `${API_URL}${path}`
}

onMounted(() => {
  loadInventory()
})

</script>