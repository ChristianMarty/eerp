import Layout from '@/layout'

const specificationPartRouter = {
  path: '/specificationPart',
  component: Layout,
  meta: {
    title: 'Specification Part',
    icon: 'list'
  },
  children: [
    {
      path: 'browser/',
      component: () => import('@/views/specificationPart/browser'),
      name: 'workOrder',
      meta: { title: 'Browser', icon: 'el-icon-sold-out' }
    },
    {
      path: 'create/',
      component: () => import('@/views/specificationPart/create'),
      name: 'create',
      meta: { title: 'Create', icon: 'edit'}
    },
    {
      path: 'item/:SpecificationPartNumber(.*)',
      component: () => import('@/views/specificationPart/item/'),
      name: 'item',
      meta: { title: 'Specification Part View', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default specificationPartRouter
