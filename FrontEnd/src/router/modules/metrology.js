import Layout from '@/layout'

const metrologyRouter = {
  path: '/metrology',
  component: Layout,
  name: 'metrology',
  meta: {
    title: 'Metrology',
    icon: 'el-icon-odometer',
    roles: ['metrology.view']
  },
  children: [
    {
      path: 'metrology',
      component: () => import('@/views/metrology/browser'),
      name: 'metrologyBrowser',
      meta: { title: 'Metrology', icon: 'el-icon-odometer' }
    },
    {
      path: 'create',
      component: () => import('@/views/metrology/create'),
      name: 'metrologyCreate',
      meta: {
        title: 'Create', icon: 'edit', roles: ['metrology.create']
      }
    },
    {
      path: 'item/:TestSystemNumber(.*)',
      component: () => import('@/views/metrology/item/'),
      name: 'metrologyView',
      meta: { title: 'Item', icon: 'el-icon-sold-out' },
      hidden: true
    }

  ]
}
export default metrologyRouter
