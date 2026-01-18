import Layout from '@/layout'

const inventoryRouter = {
  path: '/location',
  component: Layout,
  meta: {
    title: 'Location',
    icon: 'component'
  },
  children: [
    {
      path: '',
      component: () => import('@/views/location/list'),
      meta: { title: 'Location List', icon: 'search' }
    },
    {
      path: 'summary/:LocationNr(.*)',
      component: () => import('@/views/location/summary'),
      meta: { title: 'Summary', noCache: true },
      hidden: true
    },
    {
      path: 'summary',
      component: () => import('@/views/location/summary'),
      meta: { title: 'Summary', noCache: true, icon: 'search' }
    },
    {
      path: 'item/:LocationBarcode(.*)',
      component: () => import('@/views/location/item'),
      meta: { title: 'Item', noCache: true, icon: 'list' },
      hidden: true
    },
    {
      path: 'locationLabel',
      component: () => import('@/views/location/locationLabel'),
      meta: {
        title: 'Label ', icon: 'el-icon-tickets', roles: ['Renderer_Print_Location']
      }
    },
    {
      path: 'locationTransfer/',
      component: () => import('@/views/location/locationTransfer'),
      meta: { title: 'Transfer', noCache: true, icon: 'el-icon-right', roles: ['Location_Transfer'] }
    },
    {
      path: 'locationBulkTransfer/',
      component: () => import('@/views/location/locationBulkTransfer'),
      meta: { title: 'Bulk Transfer', noCache: true, icon: 'el-icon-right', roles: ['Location_BulkTransfer'] }
    }/*, {
      path: "inventorize/",
      component: () => import("@/views/locations/inventorize"),
      meta: { title: "Inventorize", noCache: true, icon: "el-icon-check" }
    }*/
  ]
}
export default inventoryRouter
