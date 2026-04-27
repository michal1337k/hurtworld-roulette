<template>
  <div class="roulette-page">
    <h1 class="roulette-title">
      Ruletka Hurtworld Legacy
    </h1>

    <div v-if="!auth.user" class="login-required">
      <p>Dostęp do ruletki tylko dla zalogowanych.</p>
      <a :href="steamLoginUrl">Zaloguj się klikając tutaj</a>
    </div>

    <div v-else class="roulette-container">
      <div v-if="loading" class="loader">
        <div class="spinner"></div>
        <p>Ładowanie przedmiotów...</p>
      </div>

      <div v-else>
        <div class="roulette">
          <div class="line"></div>

          <div
            class="items"
            :style="{
              transform: `translateX(${offset}px)`,
              transition: transitionStyle
            }"
          >
            <div
              v-for="(item, index) in rouletteItems"
              :key="index"
              class="roulette-item"
              :class="`rarity-${item.rarity}`"
            >
              <img :src="getIcon(item.icon)" />

              <span class="item-count">
                x{{ item.count }}
              </span>
            </div>
          </div>
        </div>

        <button class="roll-button" @click="roll" :disabled="rolling">
          Losuj ({{ formatRollPrice(1000) }})
        </button>

        <section class="possible-items-section">
          <h2 class="section-title">
            Możliwe przedmioty do wylosowania
          </h2>

          <div class="items-list">
            <div
              v-for="item in sortedByRarity"
              :key="item.id"
              class="item"
              :class="`rarity-${item.rarity}`"
            >
              <img :src="getIcon(item.icon)" />

              <span class="item-count">
                x{{ item.count }}
              </span>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

<!-- POPUP WINDOWS -->
<div v-if="winModal" class="modal-backdrop">
  <div class="win-modal">
    <button class="modal-close" @click="closeWinModal">×</button>

    <h2>🎉 Wygrana!</h2>

    <div
      class="modal-item"
      :class="`rarity-${winModal.rarity}`"
    >
      <img :src="getIcon(winModal.icon)" />
      <span class="item-count">x{{ winModal.count }}</span>
    </div>

    <p>{{ winModal.name }}</p>

    <button class="modal-ok" @click="closeWinModal">
      OK
    </button>
  </div>
</div>

<div v-if="errorModal" class="modal-backdrop">
  <div class="error-modal">
    <button class="modal-close" @click="closeErrorModal">x</button>

    <h2>⚠️ Błąd</h2>

    <p>{{ errorModal }}</p>

    <button class="modal-ok error-ok" @click="closeErrorModal">
      OK
    </button>
  </div>
</div>

</template>

<script setup>
import { API_URL } from '../config/api'
import { ref, computed } from 'vue'
import { auth, fetchUser } from '../store/auth'
import { onMounted } from 'vue'
import { useItems } from '../composables/useItems'

const steamLoginUrl = `${API_URL}/login/steam`
const rouletteItems = ref([])
const transitionStyle = ref('transform 4s cubic-bezier(0.1, 0.7, 0.1, 1)')
const rolling = ref(false)
const offset = ref(0)
const { items, loading, loadItems } = useItems()
const winModal = ref(null)
const errorModal = ref(null)
const rarityOrder = {

  legendary: 5,
  epic: 4,
  rare: 3,
  uncommon: 2,
  common: 1
}

const availableItems = computed(() => {
  return items.value.filter(item => Number(item.chance) > 0)
})

const sortedByRarity = computed(() => {
  return [...availableItems.value].sort(
    (a, b) => rarityOrder[b.rarity] - rarityOrder[a.rarity]
  )
})

const paddingItems = 10
const padded = []

for (let i = 0; i < paddingItems; i++) {
  padded.push(availableItems.value[Math.floor(Math.random() * availableItems.value.length)])
}

async function roll() {
  if (rolling.value) return

  if (availableItems.value.length === 0) {
    showError('Brak dostępnych przedmiotów do losowania')
    return
  }

  rolling.value = true

  try {
    const res = await fetch(`${API_URL}/api/roll`, {
      method: 'POST',
      credentials: 'include'
    })

    const data = await res.json()

    if (!res.ok || data.error) {
      showError(data.error || 'Błąd losowania')
      rolling.value = false
      return
    }

    const wonItem = data

    const fake = []

    for (let i = 0; i < 10; i++) {
      fake.push(availableItems.value[Math.floor(Math.random() * availableItems.value.length)])
    }

    for (let i = 0; i < 40; i++) {
      fake.push(availableItems.value[Math.floor(Math.random() * availableItems.value.length)])
    }

    fake.push(wonItem)

    for (let i = 0; i < 20; i++) {
      fake.push(availableItems.value[Math.floor(Math.random() * availableItems.value.length)])
    }

    rouletteItems.value = fake

    const itemWidth = 88
    const winIndex = fake.length - 21
    const centerOffset = 340

    const target = -(winIndex * itemWidth) + centerOffset - itemWidth / 2

    transitionStyle.value = 'none'
    offset.value = 0

    auth.user.balance -= 1000

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        transitionStyle.value =
          'transform 4s cubic-bezier(0.1, 0.7, 0.1, 1)'

        offset.value = target
      })
    })

    setTimeout(() => {
      winModal.value = wonItem
      rolling.value = false
    }, 4200)

  } catch (e) {
    showError('Brak połączenia z serwerem.')
    rolling.value = false
  }
}

function getIcon(path) {
  return `${API_URL}${path}`
}

function generateInitialRoll() {
  const fake = []

  for (let i = 0; i < 50; i++) {
    const random = availableItems.value[Math.floor(Math.random() * availableItems.value.length)]
    fake.push(random)
  }

  rouletteItems.value = fake
  offset.value = -120
}

function closeWinModal() {
  winModal.value = null
}

function showError(text) {
  errorModal.value = text
}

function closeErrorModal() {
  errorModal.value = null
}

function formatRollPrice(value) {
  return new Intl.NumberFormat('pl-PL', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value / 100) + ' zł'
}

onMounted(async () => {
  await loadItems()
  generateInitialRoll()
})
</script>