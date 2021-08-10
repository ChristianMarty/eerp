import Layout from '@/layout'

const documentsRouter = {
  path: '/purchasing',
  component: Layout,
  meta: {
    title: 'Purchasing',
    icon: 'el-icon-shopping-cart-2'
  },
  children: [
    {
      path: 'create/',
      component: () => import('@/views/purchasing/createPurchaseOrder'),
      name: 'createPurchaseOrder',
      meta: { title: 'Create PO', icon: 'el-icon-sold-out', roles: ['purchasing.create'] }
    },
    {
      path: 'browser/',
      component: () => import('@/views/purchasing/poBrowser'),
      name: 'purchaseBrowser',
      meta: { title: 'PO Browser', icon: 'list' }
    },
    {
      path: 'edit/:PoNo(.*)',
      component: () => import('@/views/purchasing/editPurchaseOrder'),
      name: 'editPurchaseOrder',
      meta: { title: 'Edit PO', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default documentsRouter
