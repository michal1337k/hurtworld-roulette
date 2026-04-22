<template>
<div style="padding: 20px">

    <h1>🎰 Ruletka Hurtworld Legacy</h1>

    <div v-if="message" class="message">
      {{ message }}
    </div>


    <div v-if="!auth.user">
      <p>Dostęp tylko dla zalogowanych</p>
      <a :href="steamLoginUrl">Zaloguj się</a>
    </div>
    <div v-else class="roulette-container">
    
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Ładowanie itemów...</p>
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
          </div>
        </div>
      </div>
    
      <button @click="roll" :disabled="rolling">
        🎰 Losuj (10$)
      </button>

      <div class="items-list">
        <div
          v-for="item in sortedByRarity"
          :key="item.id"
          class="item"
          :class="`rarity-${item.rarity}`"
        >
          <img :src="getIcon(item.icon)" />
        </div>
      </div>
    </div>
  </div>


  </div>
</template>

<script setup>
import { API_URL } from '../config/api'
import { ref, computed } from 'vue'
import { auth, fetchUser } from '../store/auth'
import { onMounted } from 'vue'
import { useItems } from '../composables/useItems'

const rouletteItems = ref([])
const transitionStyle = ref('transform 4s cubic-bezier(0.1, 0.7, 0.1, 1)')
const rolling = ref(false)
const offset = ref(0)
const { items, loading, loadItems } = useItems()
const message = ref(null)
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
    message.value = 'Brak dostępnych itemów do losowania'
    return
  }

  rolling.value = true
  message.value = null

  auth.user.balance -= 1000

  try {
    const res = await fetch(`${API_URL}/api/roll`, {
      method: 'POST',
      credentials: 'include'
    })

    const data = await res.json()

    if (!res.ok || data.error) {
      message.value = data.error || 'Błąd losowania'
      auth.user.balance += 1000
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

    const itemWidth = 80
    const winIndex = fake.length - 21
    const centerOffset = 300

    const target = -(winIndex * itemWidth) + centerOffset - itemWidth / 2

    transitionStyle.value = 'none'
    offset.value = 0

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        transitionStyle.value =
          'transform 4s cubic-bezier(0.1, 0.7, 0.1, 1)'

        offset.value = target
      })
    })

    setTimeout(() => {
      message.value = `Wygrałeś: ${wonItem.name}`
      rolling.value = false
    }, 4200)

  } catch (e) {
    message.value = "Brak połączenia z serwerem."
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
}

onMounted(async () => {
  await loadItems()
  generateInitialRoll()
})
</script>