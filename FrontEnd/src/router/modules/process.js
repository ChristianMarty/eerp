import Layout from '@/layout'

const processRouter = {
  path: '/process',
  component: Layout,
  name: 'process',
  meta: {
    title: 'process',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/process/list'),
      name: 'process',
      meta: { title: 'Process', icon: 'edit', roles: ['process.view'] }
    }

  ]
}
export default processRouter
