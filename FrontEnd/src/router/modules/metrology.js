import Layout from '@/layout'

const metrologyRouter = {
  path: '/metrology',
  component: Layout,
  meta: {
    title: 'Metrology',
    icon: 'el-icon-odometer'
  },
  children: [
    {
      path: 'testSystem',
      component: () => import('@/views/metrology/testSystem/list.vue'),
      meta: { title: 'Test System List', icon: 'el-icon-odometer', roles: ['Metrology_TestSystem_List'] }
    },
    {
      path: 'testSystem/create',
      component: () => import('@/views/metrology/testSystem/create.vue'),
      meta: {
        title: 'Create Test System', icon: 'el-icon-plus', roles: ['Metrology_TestSystem_Create']
      }
    },
    {
      path: 'testSystem/item/:TestSystemNumber(.*)',
      component: () => import('@/views/metrology/testSystem/item.vue'),
      meta: { title: 'Item', roles: ['Metrology_TestSystem_View'] },
      hidden: true
    }

  ]
}
export default metrologyRouter
