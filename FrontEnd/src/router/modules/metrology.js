import Layout from '@/layout'

const metrologyRouter = {
  path: '/metrology',
  component: Layout,
  name: 'metrology',
  meta: {
    title: 'Metrology',
    icon: 'el-icon-odometer'
  },
  children: [
    {
      path: 'testSystem',
      component: () => import('@/views/metrology/testSystem/list.vue'),
      name: 'testSystemList',
      meta: { title: 'Test System List', icon: 'el-icon-odometer', roles: ['Metrology_TestSystem_List'] }
    },
    {
      path: 'testSystem/create',
      component: () => import('@/views/metrology/testSystem/create.vue'),
      name: 'testSystemCreate',
      meta: {
        title: 'Create Test System', icon: 'el-icon-plus', roles: ['Metrology_TestSystem_Create']
      }
    },
    {
      path: 'testSystem/item/:TestSystemNumber(.*)',
      component: () => import('@/views/metrology/testSystem/item.vue'),
      name: 'testSystemView',
      meta: { title: 'Item', roles: ['Metrology_TestSystem_View'] },
      hidden: true
    }

  ]
}
export default metrologyRouter
