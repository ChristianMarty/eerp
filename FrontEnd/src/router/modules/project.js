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
      path: '/project',
      component: () => import('@/views/project/search'),
      name: 'projectSearch',
      meta: { title: 'Projects' }
    },
    {
      path: 'item/:ProjectNumber(.*)',
      component: () => import('@/views/project/item/'),
      name: 'item',
      meta: { title: 'Project', icon: 'el-icon-sold-out' },
      hidden: true
    }
  ]
}
export default projectRouter
