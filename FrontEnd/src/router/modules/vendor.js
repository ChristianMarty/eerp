import Layout from '@/layout'

const vendorRouter = {
  path: '/vendor',
  component: Layout,
  meta: {
    title: 'Vendor',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/vendor/list'),
      meta: { title: 'Vendor List', icon: 'search', roles: ['Vendor_List'] }
    },
    {
      path: 'create',
      component: () => import('@/views/vendor/create'),
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['Vendor_Create'] }
    },
    {
      path: 'view/:vendorNo(.*)',
      component: () => import('@/views/vendor/item/'),
      meta: { title: 'Item', icon: 'el-icon-sold-out', roles: ['Vendor_View'] },
      hidden: true
    }

  ]
}
export default vendorRouter
