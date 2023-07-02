import Layout from '@/layout'

const partsRouter = {
  path: '/productionPart',
  component: Layout,
  name: 'parts',
  meta: {
    title: 'Production Part',
    icon: 'component'
  },
  children: [
    {
      path: 'search',
      component: () => import('@/views/productionPart/search'),
      name: 'productionPartSearch',
      meta: { title: 'Search', icon: 'search' }
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
