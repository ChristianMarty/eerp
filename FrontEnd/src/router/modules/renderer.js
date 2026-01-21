import Layout from '@/layout'

const stockRouter = {
  path: '/renderer',
  component: Layout,
  meta: {
    title: 'Renderer',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/renderer/list'),
      name: 'rendererList',
      meta: { title: 'Renderer', icon: 'list' }
    },
    {
      path: ':Id(.*)',
      component: () => import('@/views/renderer/item'),
      name: 'rendererItem',
      meta: { title: 'Label', icon: 'list' },
      hidden: true
    }
  ]
}
export default stockRouter
