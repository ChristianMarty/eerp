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
      path: 'add',
      component: () => import('@/views/stock/add'),
      name: 'add',
      meta: { title: 'Add', icon: 'el-icon-plus', roles: ['stock.add'] }
    },
    {
      path: 'remove',
      component: () => import('@/views/stock/remove'),
      name: 'remove',
      meta: { title: 'Remove', icon: 'el-icon-minus', roles: ['stock.remove'] }
    },
    {
      path: 'count',
      component: () => import('@/views/stock/count'),
      name: 'count',
      meta: { title: 'Count', icon: 'el-icon-finished', roles: ['stock.count'] }
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
