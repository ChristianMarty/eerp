import Layout from '@/layout'

const processRouter = {
  path: '/process',
  component: Layout,
  name: 'process',
  meta: {
    title: 'process',
    icon: 'component',
    roles: ['process.run']
  },
  children: [
    {
      path: 'process',
      component: () => import('@/views/process/index'),
      name: 'process',
      meta: { title: 'Process', icon: 'edit' }
    }

  ]
}
export default processRouter
