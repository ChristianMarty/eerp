import Layout from '@/layout'

const reportRouter = {
  path: '/report',
  component: Layout,
  name: 'report',
  meta: {
    title: 'report',
    icon: 'el-icon-download'
    // roles: ["report"]
  },
  children: [
    {
      path: 'report',
      component: () => import('@/views/report/index'),
      name: 'report',
      meta: { title: 'Report', icon: 'el-icon-download' }
    }

  ]
}
export default reportRouter
