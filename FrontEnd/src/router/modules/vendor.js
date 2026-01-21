import Layout from '@/layout'

const vendorRouter = {
  path: '/vendor',
  component: Layout,
  name: 'vendor',
  meta: {
    title: 'Vendor',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/vendor/list'),
      name: 'vendor',
      meta: { title: 'Vendor List', icon: 'search', roles: ['Vendor_List'] }
    },
    {
      path: 'create',
      component: () => import('@/views/vendor/create'),
      name: 'vendorCreate',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['Vendor_Create'] }
    },
    {
      path: 'view/:vendorNo(.*)',
      component: () => import('@/views/vendor/item/'),
      name: 'vendorView',
      meta: { title: 'Item', icon: 'el-icon-sold-out', roles: ['Vendor_View'] },
      hidden: true
    }

  ]
}
export default vendorRouter
