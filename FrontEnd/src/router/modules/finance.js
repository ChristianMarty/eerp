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
      path: 'summary/',
      component: () => import('@/views/finance/view'),
      meta: { title: 'Summary', icon: 'el-icon-sold-out', roles: ['Finance_View'] }
    },
    {
      path: 'costCenter/',
      component: () => import('@/views/finance/costCenterBrowser'),
      meta: { title: 'Cost Center', icon: 'el-icon-sold-out', roles: ['Finance_CostCenter_View'] }
    },
    {
      path: 'costCenter/item/:CostCenterNumber(.*)',
      component: () => import('@/views/finance/costCenterItem'),
      meta: {
        title: 'Cost Center',
        noCache: true,
        activeMenu: '/parts/partBrowser',
        roles: ['Finance_CostCenter_View']
      },
      hidden: true
    }
  ]
}
export default financeRouter
