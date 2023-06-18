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
      component: () => import('@/views/purchasing/browser'),
      name: 'purchaseBrowser',
      meta: { title: 'PO Search', icon: 'search' }
    },
    {
      path: 'create/',
      component: () => import('@/views/purchasing/create'),
      name: 'createPurchaseOrder',
      meta: { title: 'Create', icon: 'edit', roles: ['purchasing.create'] }
    },
    {
      path: 'orderRequest/',
      component: () => import('@/views/purchasing/orderRequest'),
      name: 'orderRequest',
      meta: { title: 'Order Request', icon: 'list' }
    },
    {
      path: 'edit/:PoNo(.*)',
      component: () => import('@/views/purchasing/edit'),
      name: 'editPurchaseOrder',
      meta: { title: 'Edit PO', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default purchasingRouter
