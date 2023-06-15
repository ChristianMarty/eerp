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
      name: 'finance',
      meta: { title: 'Summary', icon: 'el-icon-sold-out', roles: ['finance.view'] }
    },
    {
      path: 'costCenter/',
      component: () => import('@/views/finance/costCenterBrowser'),
      name: 'finance',
      meta: { title: 'Cost Center', icon: 'el-icon-sold-out', roles: ['finance.costCenter'] }
    },
    {
      path: 'costCenter/item/:CostCenterNumber(.*)',
      component: () => import('@/views/finance/costCenterItem'),
      name: 'CostCenter',
      meta: {
        title: 'Cost Center',
        noCache: true,
        activeMenu: '/parts/partBrowser',
        roles: ['finance.costCenter']
      },
      hidden: true
    }
  ]
}
export default financeRouter
