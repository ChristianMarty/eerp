import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

/* Layout */
import Layout from '@/layout'

/* Router Modules */
import purchasingRouter from './modules/purchasing'
import vendorRouter from './modules/vendor'
import prodPartsRouter from './modules/productionPart'
import manufacturerPartRouter from './modules/manufacturerPart'
import inventoryRouter from './modules/inventory'
import documentRouter from './modules/document'
import locationsRouter from './modules/location'
import stockRouter from './modules/stock'
import partMetadataRouter from './modules/partMetadata'
import rendererRouter from './modules/renderer'
import processRouter from './modules/process'
import reportRouter from './modules/report'
import projectRouter from './modules/project'
import billOfMaterial from './modules/billOfMaterial'
import workOrderRouter from './modules/workOrder'
import assemblyRouter from './modules/assembly'
import metrologyRouter from './modules/metrology'
import financeRouter from './modules/finance'
import specificationPartRouter from './modules/specificationPart'

/**
 * Note: sub-menu only appear when route children.length >= 1
 * Detail see: https://panjiachen.github.io/vue-element-admin-site/guide/essentials/router-and-nav.html
 *
 * hidden: true                   if set true, item will not show in the sidebar(default is false)
 * alwaysShow: true               if set true, will always show the root menu
 *                                if not set alwaysShow, when item has more than one children route,
 *                                it will becomes nested mode, otherwise not show the root menu
 * redirect: noRedirect           if set noRedirect will no redirect in the breadcrumb
 * name:'router-name'             the name is used by <keep-alive> (must set!!!)
 * meta : {
    roles: ['admin','editor']    control the page roles (you can set multiple roles)
    title: 'title'               the name show in sidebar and breadcrumb (recommend set)
    icon: 'svg-name'/'el-icon-x' the icon show in the sidebar
    noCache: true                if set true, the page will no be cached(default is false)
    affix: true                  if set true, the tag will affix in the tags-view
    breadcrumb: false            if set false, the item will hidden in breadcrumb(default is true)
    activeMenu: '/example/list'  if set path, the sidebar will highlight the path you set
  }
 */

/**
 * constantRoutes
 * a base page that does not have permission requirements
 * all roles can be accessed
 */
export const constantRoutes = [
  {
    path: '/redirect',
    component: Layout,
    hidden: true,
    children: [
      {
        path: '/redirect/:path(.*)',
        component: () => import('@/views/redirect/index')
      }
    ]
  },
  {
    path: '/login',
    component: () => import('@/views/login/index'),
    hidden: true
  },
  {
    path: '/404',
    component: () => import('@/views/error-page/404'),
    hidden: true
  },
  {
    path: '/401',
    component: () => import('@/views/error-page/401'),
    hidden: true
  },
  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    children: [
      {
        path: 'dashboard',
        component: () => import('@/views/dashboard/index'),
        name: 'Dashboard',
        meta: { title: 'Dashboard', icon: 'dashboard', affix: true }
      },
      {
        path: 'search/:Search(.*)',
        component: () => import('@/views/search/index'),
        name: 'Search',
        meta: { title: 'Search', icon: 'search', noCache: true },
        hidden: true
      }
    ]
  },
  {
    path: '/profile',
    component: Layout,
    redirect: '/profile/index',
    hidden: true,
    children: [
      {
        path: 'index',
        component: () => import('@/views/profile/index'),
        name: 'Profile',
        meta: { title: 'Profile', icon: 'user', noCache: true }
      }
    ]
  }
]

/**
 * asyncRoutes
 * the routes that need to be dynamically loaded based on user roles
 */

export const asyncRoutes = [
  purchasingRouter,
  vendorRouter,
  financeRouter,
  projectRouter,
  billOfMaterial,
  workOrderRouter,
  prodPartsRouter,
  manufacturerPartRouter,
  specificationPartRouter,
  partMetadataRouter,
  stockRouter,
  assemblyRouter,
  inventoryRouter,
  metrologyRouter,
  locationsRouter,
  documentRouter,
  rendererRouter,
  processRouter,
  reportRouter,

  // 404 page must be placed at the end !!!
  { path: '*', redirect: '/404', hidden: true }
]

const createRouter = () =>
  new Router({
    // mode: 'history', // require service support
    scrollBehavior: () => ({ y: 0 }),
    routes: constantRoutes
  })

const router = createRouter()

// Detail see: https://github.com/vuejs/vue-router/issues/1234#issuecomment-357941465
export function resetRouter() {
  const newRouter = createRouter()
  router.matcher = newRouter.matcher // reset router
}

export default router
