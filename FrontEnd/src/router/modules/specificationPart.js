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
      meta: { title: 'Spec Part Search', icon: 'search', roles: ['SpecificationPart_View'] }
    },
    {
      path: 'create/',
      component: () => import('@/views/specificationPart/create'),
      name: 'specificationPartCreate',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['SpecificationPart_Create'] }
    },
    {
      path: 'item/:SpecificationPartBarcode(.*)',
      component: () => import('@/views/specificationPart/item'),
      name: 'specificationPartItem',
      meta: { title: 'Specification Part View', roles: ['SpecificationPart_View'] },
      hidden: true
    }
  ]
}
export default specificationPartRouter
