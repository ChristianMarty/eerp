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
      path: '',
      component: () => import('@/views/inventory/list'),
      name: 'Inventorylist',
      meta: { title: 'Inventory List', icon: 'search' }
    },
    {
      path: 'inventoryCreate/',
      component: () => import('@/views/inventory/inventoryCreate'),
      name: 'inventoryCreateNew',
      meta: { title: 'Create ', noCache: true, icon: 'edit', roles: ['inventory.create'] }

    },
    {
      path: 'inventoryCreate/:invNo(.*)',
      component: () => import('@/views/inventory/inventoryCreate'),
      name: 'inventoryCreate',
      meta: { title: 'Create ', noCache: true, icon: 'component' },
      hidden: true
    },
    {
      path: 'label',
      component: () => import('@/views/inventory/label'),
      name: 'inventoryLabel',
      meta: { title: 'Label ', icon: 'el-icon-tickets', roles: ['inventory.print'] }

    },
    {
      path: 'item/:invNo(.*)',
      component: () => import('@/views/inventory/item'),
      name: 'inventoryView',
      meta: {
        title: 'Item',
        noCache: true,
        activeMenu: '/inventory/item'
      },
      hidden: true
    }
  ]
}
export default inventoryRouter
