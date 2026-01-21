import Layout from '@/layout'

const partsRouter = {
  path: '/productionPart',
  component: Layout,
  meta: {
    title: 'Production Part',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/productionPart/list'),
      name: 'productionPartSearch',
      meta: { title: 'Search', icon: 'search' }
    },
    {
      path: 'createProductionPart',
      component: () => import('@/views/productionPart/create'),
      name: 'createProductionPart',
      meta: { title: 'Create', icon: 'edit', roles: ['ProductionPart_Create'] }
    },
    {
      path: 'prodPartNotification',
      component: () => import('@/views/parts/prodPartNotification'),
      name: 'prodPartNotification',
      meta: { title: 'Stock Notification', icon: 'list' }
    },
    {
      path: 'item/:productionPartNumber(.*)',
      component: () => import('@/views/productionPart/item'),
      name: 'productionPart',
      meta: {
        title: 'Production Part',
        noCache: true,
        activeMenu: '/productionPart/productionPartSearch'
      },
      hidden: true
    }
  ]
}
export default partsRouter
