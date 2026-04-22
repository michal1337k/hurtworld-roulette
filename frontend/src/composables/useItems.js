import { ref } from 'vue'
import { fetchItems } from '../api/items'

const items = ref([])
const loading = ref(false)
const loaded = ref(false)

export function useItems() {

  async function loadItems() {
    if (loaded.value) return 

    loading.value = true

    try {
      items.value = await fetchItems()
      loaded.value = true
    } catch (e) {
      console.error(e)
    } finally {
      loading.value = false
    }
  }

  function resetItems() {
    loaded.value = false
    }

  return {
    items,
    loading,
    loadItems,
    resetItems
  }
}