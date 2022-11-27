import Layout from '@/layout'

const vendorRouter = {
  path: '/vendor',
  component: Layout,
  name: 'vendor',
  meta: {
    title: 'Vendor',
    icon: 'component',
    roles: ['vendor.view']
  },
  children: [
    {
      path: 'vendor',
      component: () => import('@/views/vendor/browser'),
      name: 'vendor',
      meta: { title: 'Vendor Browser', icon: 'list' }
    },
    {
      path: 'create',
      component: () => import('@/views/vendor/create'),
      name: 'vendorCreate',
      meta: {
        title: 'Create Vendor', icon: 'edit', roles: ['vendor.create']
      }
    },
    {
      path: 'view/:vendorNo(.*)',
      component: () => import('@/views/vendor/view/'),
      name: 'vendorView',
      meta: { title: 'Vendor View', icon: 'el-icon-sold-out' },
      hidden: true
    }

  ]
}
export default vendorRouter
