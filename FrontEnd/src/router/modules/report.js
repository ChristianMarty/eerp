import Layout from '@/layout'

const reportRouter = {
  path: '/report',
  component: Layout,
  meta: {
    title: 'report',
    icon: 'el-icon-download'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/report/list'),
      name: 'reportList',
      meta: { title: 'Report', icon: 'el-icon-download', roles: ['Report_List'] }
    }

  ]
}
export default reportRouter
