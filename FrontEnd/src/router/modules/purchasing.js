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
      name: 'purchaseOrderList',
      meta: { title: 'Order List', icon: 'search', roles: ['purchasing.view'] }
    },
    {
      path: 'create/',
      component: () => import('@/views/purchasing/create'),
      name: 'createPurchaseOrder',
      meta: { title: 'Create Order', icon: 'el-icon-plus', roles: ['purchasing.create'] }
    },
    {
      path: 'edit/:PurchaseOrderNumber(.*)',
      component: () => import('@/views/purchasing/item'),
      name: 'editPurchaseOrder',
      meta: { title: 'Edit Order', icon: 'el-icon-sold-out', roles: ['purchasing.view'] },
      hidden: true
    }
  ]
}
export default purchasingRouter
