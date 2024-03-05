import Layout from '@/layout'

const projectRouter = {
  path: '/workOrder',
  component: Layout,
  meta: {
    title: 'Work Order',
    icon: 'list'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/workOrder/list'),
      name: 'workOrder',
      meta: { title: 'Work Order', icon: 'el-icon-sold-out' }
    },
    {
      path: 'create/',
      component: () => import('@/views/workOrder/create'),
      name: 'create',
      meta: { title: 'Create', icon: 'edit', roles: ['workOrder.create'] }
    },
    {
      path: 'item/:workOrderNo(.*)',
      component: () => import('@/views/workOrder/item/'),
      name: 'workOrderView',
      meta: { title: 'Work Order View', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default projectRouter
