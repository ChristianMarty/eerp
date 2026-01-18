import Layout from '@/layout'

const processRouter = {
  path: '/process',
  component: Layout,
  meta: {
    title: 'process',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/process/list'),
      meta: { title: 'Process', icon: 'edit', roles: ['Process_List'] }
    }

  ]
}
export default processRouter
