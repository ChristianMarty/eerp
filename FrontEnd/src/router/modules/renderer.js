import Layout from '@/layout'

const stockRouter = {
  path: '/renderer',
  component: Layout,
  name: 'label',
  meta: {
    title: 'Renderer',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/renderer/list'),
      name: 'label',
      meta: { title: 'Renderer', icon: 'list' }
    },
    {
      path: ':Id(.*)',
      component: () => import('@/views/renderer/item'),
      name: 'Label',
      meta: { title: 'Label', icon: 'list' },
      hidden: true
    }
  ]
}
export default stockRouter
