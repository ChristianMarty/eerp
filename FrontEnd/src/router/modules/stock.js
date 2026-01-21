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
      meta: { title: 'Stock List', icon: 'search', roles: ['Stock_List'] }
    },
    {
      path: 'item',
      component: () => import('@/views/stock/item'),
      name: 'itemNew',
      meta: { title: 'Item', icon: 'el-icon-finished', roles: ['Stock_View'] }
    },
    {
      path: 'create',
      component: () => import('@/views/stock/create'),
      name: 'create',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['Stock_Create'] }
    },
    {
      path: 'bulkRemove',
      component: () => import('@/views/stock/bulkRemove'),
      name: 'bulkRemove',
      meta: { title: 'Bulk Remove', icon: 'el-icon-minus', roles: ['Stock_History_Remove'] }
    },
    {
      path: 'countingRequest',
      component: () => import('@/views/stock/countingRequest'),
      name: 'countingRequest',
      meta: { title: 'Counting Requests', icon: 'el-icon-files', roles: ['Stock_History_Count'] }
    },
    {
      path: 'item/:StockNumber(.*)',
      component: () => import('@/views/stock/item'),
      name: 'item',
      meta: { title: 'Item', roles: ['Stock_View'] },
      hidden: true
    }
  ]
}
export default stockRouter
