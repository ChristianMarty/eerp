import Layout from '@/layout'

const projectRouter = {
  path: '/project',
  component: Layout,
  meta: {
    title: 'Projects',
    icon: 'list'
  },
  children: [
    {
      path: 'browser/',
      component: () => import('@/views/project/projectBrowser'),
      name: 'projectBrowser',
      meta: { title: 'Project Browser', icon: 'el-icon-sold-out' }
    },
    {
      path: 'projectView/:projectNo(.*)',
      component: () => import('@/views/project/projectView/'),
      name: 'projectView',
      meta: { title: 'Project View', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default projectRouter
