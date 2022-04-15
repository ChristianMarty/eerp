import Layout from '@/layout'

const supplierRouter = {
  path: '/supplier',
  component: Layout,
  name: 'supplier',
  meta: {
    title: 'Supplier',
    icon: 'component',
    roles: ['supplier.view']
  },
  children: [
    {
      path: 'supplier',
      component: () => import('@/views/supplier/supplierBrowser'),
      name: 'supplier',
      meta: { title: 'Supplier Browser', icon: 'list' }
    },
    {
      path: 'create',
      component: () => import('@/views/supplier/create'),
      name: 'supplierCreate',
      meta: {
        title: 'Create Supplier', icon: 'edit', roles: ['supplier.create']
      }
    },
    {
      path: 'supplierView/:supplierNo(.*)',
      component: () => import('@/views/supplier/supplierView/'),
      name: 'projectView',
      meta: { title: 'Supplier View', icon: 'el-icon-sold-out' },
      hidden: true
    }

  ]
}
export default supplierRouter
