import { createRouter, createWebHistory } from 'vue-router'
import NotesView from '../views/NotesView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      component: NotesView
    }
  ]
})

export default router