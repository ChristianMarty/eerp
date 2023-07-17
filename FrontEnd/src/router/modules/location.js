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
      path: 'search',
      component: () => import('@/views/location/search'),
      name: 'locationSearch',
      meta: { title: 'Search', icon: 'search' }
    },
    {
      path: 'summary/:LocationNr(.*)',
      component: () => import('@/views/location/summary'),
      name: 'summary',
      meta: { title: 'Summary', noCache: true },
      hidden: true
    },
    {
      path: 'summary',
      component: () => import('@/views/location/summary'),
      name: 'summary',
      meta: { title: 'Summary', noCache: true, icon: 'search' }
    },
    {
      path: 'item/:LocationBarcode(.*)',
      component: () => import('@/views/location/item'),
      name: 'locationItem',
      meta: { title: 'Location Item', noCache: true, icon: 'list' },
      hidden: true
    },
    {
      path: 'locationLabel',
      component: () => import('@/views/location/locationLabel'),
      name: 'locationLabel',
      meta: {
        title: 'Label ', icon: 'el-icon-tickets', roles: ['location.print']
      }
    },
    {
      path: 'locationTransfer/',
      component: () => import('@/views/location/locationTransfer'),
      name: 'locationTransfer',
      meta: { title: 'Transfer', noCache: true, icon: 'el-icon-right', roles: ['location.transfer'] }
    },
    {
      path: 'locationBulkTransfer/',
      component: () => import('@/views/location/locationBulkTransfer'),
      name: 'locationBulkTransfer',
      meta: { title: 'Bulk Transfer', noCache: true, icon: 'el-icon-right', roles: ['location.bulkTransfer'] }
    }/*, {
      path: "inventorize/",
      component: () => import("@/views/locations/inventorize"),
      name: "inventorize",
      meta: { title: "Inventorize", noCache: true, icon: "el-icon-check" }
    }*/
  ]
}
export default inventoryRouter
