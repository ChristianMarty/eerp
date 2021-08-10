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
      path: 'locationBrowser',
      component: () => import('@/views/location/locationBrowser'),
      name: 'locationBrowser',
      meta: { title: 'Location Browser', icon: 'list' }
    },
    {
      path: 'summary/:LocationNr(.*)',
      component: () => import('@/views/location/summary'),
      name: 'summary',
      meta: { title: 'Summary', icon: 'list' }
    },
    {
      path: 'locationLabel',
      component: () => import('@/views/location/locationLabel'),
      name: 'locationLabel',
      meta: { title: 'Label ', icon: 'el-icon-tickets' }
    },
    {
      path: 'locationTransfer/',
      component: () => import('@/views/location/locationTransfer'),
      name: 'locationTransfer',
      meta: { title: 'Location Transfer', noCache: true, icon: 'el-icon-right' }
    }/*, {
      path: "inventorize/",
      component: () => import("@/views/locations/inventorize"),
      name: "inventorize",
      meta: { title: "Inventorize", noCache: true, icon: "el-icon-check" }
    }*/
  ]
}
export default inventoryRouter
