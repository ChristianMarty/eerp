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
      meta: { title: 'Search', icon: 'search' }
    },
    {
      path: 'createProductionPart',
      component: () => import('@/views/productionPart/create'),
      meta: { title: 'Create', icon: 'edit', roles: ['ProductionPart_Create'] }
    },
    {
      path: 'prodPartNotification',
      component: () => import('@/views/parts/prodPartNotification'),
      meta: { title: 'Stock Notification', icon: 'list' }
    },
    {
      path: 'item/:productionPartNumber(.*)',
      component: () => import('@/views/productionPart/item'),
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
