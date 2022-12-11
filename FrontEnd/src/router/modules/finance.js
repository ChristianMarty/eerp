import Layout from '@/layout'

const financeRouter = {
  path: '/finance',
  component: Layout,
  meta: {
    title: 'Finance',
    icon: 'list'
  },
  children: [
    {
      path: 'browser/',
      component: () => import('@/views/finance/view'),
      name: 'finance',
      meta: { title: 'Finance', icon: 'el-icon-sold-out', roles: ['finance.view'] }
    }
  ]
}
export default financeRouter
