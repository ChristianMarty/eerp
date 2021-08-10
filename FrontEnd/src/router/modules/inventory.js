import Layout from '@/layout'

const inventoryRouter = {
  path: '/inventory',
  component: Layout,
  meta: {
    title: 'Inventory',
    icon: 'component'
  },
  children: [
    {
      path: 'inventoryBrowser',
      component: () => import('@/views/inventory/inventoryBrowser'),
      name: 'inventoryBrowser',
      meta: { title: 'Inventory Browser', icon: 'list' }
    },
    {
      path: 'inventoryCreate/',
      component: () => import('@/views/inventory/inventoryCreate'),
      name: 'inventoryCreateNew',
      meta: { title: 'Create ', noCache: true, icon: 'component', roles: ['inventory.create'] }

    },
    {
      path: 'inventoryCreate/:invNo(.*)',
      component: () => import('@/views/inventory/inventoryCreate'),
      name: 'inventoryCreate',
      meta: { title: 'Create ', noCache: true, icon: 'component' },
      hidden: true
    },
    {
      path: 'inventoryLabel',
      component: () => import('@/views/inventory/inventoryLabel'),
      name: 'inventoryLabel',
      meta: { title: 'Label ', icon: 'el-icon-tickets', roles: ['inventory.print'] }

    },
    {
      path: 'inventoryView/:invNo(.*)',
      component: () => import('@/views/inventory/inventoryView'),
      name: 'inventoryView',
      meta: {
        title: 'Inventory View',
        noCache: true,
        activeMenu: '/inventory/inventoryView'
      },
      hidden: true
    }
  ]
}
export default inventoryRouter
