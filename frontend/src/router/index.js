import { createRouter, createWebHistory } from 'vue-router'

import Home from '../pages/Home.vue'
import Admin from '../pages/Admin.vue'
import AddItem from '../pages/AddItem.vue'
import { auth } from '../store/auth'

const routes = [
  { path: '/', component: Home },
  { path: '/acp', component: Admin, meta: { requiresAuth: true, requiresAdmin: true }},
  { path: '/acp/add-item', component: AddItem, meta: { requiresAuth: true, requiresAdmin: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from) => {

  if (!auth.loaded) return false

  const isLogged = !!auth.user
  const isAdmin = auth.user?.roles?.includes('ROLE_ADMIN')

  if (to.meta.requiresAuth && !isLogged) {
    return { path: '/' }
  }

  if (to.meta.requiresAdmin && !isAdmin) {
    return { path: '/' }
  }

  return true
})

export default router