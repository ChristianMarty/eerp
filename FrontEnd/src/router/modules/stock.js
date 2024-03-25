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
      path: 'list',
      component: () => import('@/views/stock/list'),
      name: 'stockList',
      meta: { title: 'Stock List', icon: 'search' }
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
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['stock.create'] }
    },
    {
      path: 'bulkRemove',
      component: () => import('@/views/stock/bulkRemove'),
      name: 'bulkRemove',
      meta: { title: 'Bulk Remove', icon: 'el-icon-minus', roles: ['stock.remove'] }
    },
    {
      path: 'item/:StockNumber(.*)',
      component: () => import('@/views/stock/item'),
      name: 'item',
      meta: { title: 'Item', icon: 'el-icon-finished' },
      hidden: true
    }
  ]
}
export default stockRouter
