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
      name: 'workOrderList',
      meta: { title: 'Work Order', icon: 'el-icon-sold-out' }
    },
    {
      path: 'create/',
      component: () => import('@/views/workOrder/create'),
      name: 'workOrderCreate',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['WorkOrder_Create'] }
    },
    {
      path: 'item/:workOrderNo(.*)',
      component: () => import('@/views/workOrder/item'),
      name: 'workOrderItem',
      meta: { title: 'Work Order View', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default projectRouter
