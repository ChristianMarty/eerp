import Layout from '@/layout'

const partsRouter = {
  path: '/mfrParts',
  component: Layout,
  name: 'mfrParts',
  meta: {
    title: 'Manufacturer Part',
    icon: 'component'
  },
  children: [
    {
      path: 'partBrowser',
      component: () => import('@/views/parts/partBrowser'),
      name: 'partBrowser',
      meta: { title: 'Part Browser', icon: 'list' }
    },
    {
      path: 'create',
      component: () => import('@/views/parts/create'),
      name: 'create',
      meta: { title: 'Create Part', icon: 'edit', roles: ['manufacturerPart.create'] }
    },
    {
      path: 'partView/:partId(.*)',
      component: () => import('@/views/parts/partView'),
      name: 'partView',
      meta: {
        title: 'Part View',
        noCache: true,
        activeMenu: '/parts/partBrowser'
      },
      hidden: true
    }
  ]
}
export default partsRouter
