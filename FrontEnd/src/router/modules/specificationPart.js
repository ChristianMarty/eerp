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
      name: 'specificationPart',
      meta: { title: 'Spec Part Search', icon: 'search', roles: ['specificationPart.view'] }
    },
    {
      path: 'create/',
      component: () => import('@/views/specificationPart/create'),
      name: 'create',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['specificationPart.create'] }
    },
    {
      path: 'item/:SpecificationPartBarcode(.*)',
      component: () => import('@/views/specificationPart/item/'),
      name: 'item',
      meta: { title: 'Specification Part View', icon: 'el-icon-sold-out', roles: ['specificationPart.view'] },
      hidden: true
    }
  ]
}
export default specificationPartRouter
