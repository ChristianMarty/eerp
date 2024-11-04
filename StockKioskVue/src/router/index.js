import { createRouter, createWebHashHistory } from 'vue-router'
import HomeView from '../views/home.vue'
import RemoveStockView from '../views/removeStock/removeStock.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/removeStock',
    name: 'removeStock',
    component: RemoveStockView
  },
  {
    path: '/countStock',
    name: 'countStock',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: function () {
      return import(/* webpackChunkName: "about" */ '../views/countStock/countStock.vue')
    }
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
