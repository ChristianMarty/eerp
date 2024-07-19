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
      path: 'testSystem',
      component: () => import('@/views/metrology/testSystem/list.vue'),
      name: 'testSystemList',
      meta: { title: 'Test System List', icon: 'el-icon-odometer' }
    },
    {
      path: 'testSystem/create',
      component: () => import('@/views/metrology/testSystem/create.vue'),
      name: 'testSystemCreate',
      meta: {
        title: 'Create Test System', icon: 'el-icon-plus', roles: ['metrology.create']
      }
    },
    {
      path: 'testSystem/item/:TestSystemNumber(.*)',
      component: () => import('@/views/metrology/testSystem/item.vue'),
      name: 'testSystemView',
      meta: { title: 'Item' },
      hidden: true
    }

  ]
}
export default metrologyRouter
