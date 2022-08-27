import Layout from '@/layout'

const stockRouter = {
  path: '/stock',
  component: Layout,
  name: 'stock',
  meta: {
    title: 'Stock',
    icon: 'el-icon-box'
  },
  children: [
    {
      path: 'search',
      component: () => import('@/views/stock/search'),
      name: 'search',
      meta: { title: 'Search', icon: 'search' }
    },
    {
      path: 'item',
      component: () => import('@/views/stock/item'),
      name: 'itemNew',
      meta: { title: 'Item', icon: 'el-icon-finished' }
    },
    {
      path: 'create',
      component: () => import('@/views/stock/create'),
      name: 'create',
      meta: { title: 'Create', icon: 'edit', roles: ['stock.create'] }
    },
    {
      path: 'item/:StockNo(.*)',
      component: () => import('@/views/stock/item'),
      name: 'item',
      meta: { title: 'Item', icon: 'el-icon-finished' },
      hidden: true
    }
  ]
}
export default stockRouter
