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
      meta: { title: 'Inventory List', icon: 'search' }
    },
    {
      path: 'inventoryCreate/',
      component: () => import('@/views/inventory/inventoryCreate'),
      meta: { title: 'Create ', noCache: true, icon: 'edit', roles: ['Inventory_Create'] }

    },
    {
      path: 'inventoryCreate/:invNo(.*)',
      component: () => import('@/views/inventory/inventoryCreate'),
      meta: { title: 'Create ', noCache: true, icon: 'el-icon-plus' },
      hidden: true
    },
    {
      path: 'label',
      component: () => import('@/views/inventory/label'),
      meta: { title: 'Label ', icon: 'el-icon-tickets', roles: ['Renderer_Print_Inventory'] }

    },
    {
      path: 'item/:invNo(.*)',
      component: () => import('@/views/inventory/item'),
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
