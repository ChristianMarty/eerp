import Layout from '@/layout'

const stockRouter = {
  path: '/label',
  component: Layout,
  name: 'label',
  meta: {
    title: 'Label',
    icon: 'component'
  },
  children: [
    {
      path: 'browser',
      component: () => import('@/views/label/labelBrowser'),
      name: 'label',
      meta: { title: 'Label Browser', icon: 'list' }
    },
    {
      path: ':Id(.*)',
      component: () => import('@/views/label/item'),
      name: 'Label',
      meta: { title: 'Label', icon: 'list' },
      hidden: true
    }
  ]
}
export default stockRouter
