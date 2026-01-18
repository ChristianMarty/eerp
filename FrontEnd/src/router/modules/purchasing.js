import Layout from '@/layout'

const purchasingRouter = {
  path: '/purchasing',
  component: Layout,
  meta: {
    title: 'Purchasing',
    icon: 'el-icon-shopping-cart-2'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/purchasing/list'),
      meta: { title: 'Order List', icon: 'search', roles: ['PurchaseOrder_List'] }
    },
    {
      path: 'create/',
      component: () => import('@/views/purchasing/create'),
      meta: { title: 'Create Order', icon: 'el-icon-plus', roles: ['PurchaseOrder_Create'] }
    },
    {
      path: 'edit/:PurchaseOrderNumber(.*)',
      component: () => import('@/views/purchasing/item'),
      meta: { title: 'Edit Order', icon: 'el-icon-sold-out', roles: ['PurchaseOrder_View'] },
      hidden: true
    }
  ]
}
export default purchasingRouter
