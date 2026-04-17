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
</template>

<script setup>
import { API_URL } from '../config/api'
import { ref, computed } from 'vue'
import { auth, fetchUser } from '../store/auth'
import { onMounted } from 'vue'

const rouletteItems = ref([])
const transitionStyle = ref('transform 4s cubic-bezier(0.1, 0.7, 0.1, 1)')
const rolling = ref(false)
const offset = ref(0)
const items = ref([])
const message = ref(null)
const rarityOrder = {

  legendary: 5,
  epic: 4,
  rare: 3,
  uncommon: 2,
  common: 1
}

const sortedByRarity = computed(() => {
  return [...items.value].sort(
    (a, b) => rarityOrder[b.rarity] - rarityOrder[a.rarity]
  )
})

const paddingItems = 10
const padded = []

for (let i = 0; i < paddingItems; i++) {
  padded.push(items.value[Math.floor(Math.random() * items.value.length)])
}

async function roll() {
  if (rolling.value) return

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
      fake.push(items.value[Math.floor(Math.random() * items.value.length)])
    }

    for (let i = 0; i < 40; i++) {
      fake.push(items.value[Math.floor(Math.random() * items.value.length)])
    }

    fake.push(wonItem)

    for (let i = 0; i < 20; i++) {
      fake.push(items.value[Math.floor(Math.random() * items.value.length)])
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

async function loadItems() {
  const res = await fetch(`${API_URL}/api/admin/items`, {
    credentials: 'include'
  })

  items.value = await res.json()
}

function getIcon(path) {
  return `${API_URL}${path}`
}

function generateInitialRoll() {
  const fake = []

  for (let i = 0; i < 50; i++) {
    const random = items.value[Math.floor(Math.random() * items.value.length)]
    fake.push(random)
  }

  rouletteItems.value = fake
}

onMounted(async () => {
  await loadItems()
  generateInitialRoll()
})
</script>

<style>
.roulette {
  width: 600px;
  overflow: hidden;
  margin: 20px auto;
  position: relative;
  border: 2px solid #333;
}

.items {
  display: flex;
  align-items: center;
}

.roulette-item {
  width: 80px;
  height: 80px;
  flex: 0 0 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.line {
  position: absolute;
  left: 50%;
  top: 0;
  width: 2px;
  height: 100%;
  background: red;
  z-index: 2;
}
.items-list {
  display: grid;
  grid-template-columns: repeat(6, 60px);
  gap: 10px;
  justify-content: center;
}

.item {
  width: 60px;
  height: 60px;
}
.roulette-item img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
</style>