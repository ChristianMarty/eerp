import Layout from '@/layout'

const partsRouter = {
  path: '/prodParts',
  component: Layout,
  name: 'parts',
  meta: {
    title: 'Production Part',
    icon: 'component'
  },
  children: [
    {
      path: 'prodPartBrowser',
      component: () => import('@/views/parts/prodPartBrowser'),
      name: 'prodPartBrowser',
      meta: { title: 'Production Part Browser', icon: 'list' }
    },
    {
      path: 'bomView',
      component: () => import('@/views/parts/bomView'),
      name: 'bomView',
      meta: { title: 'Bom View', icon: 'list' }
    },
    {
      path: 'prodPartView/:partNo(.*)',
      component: () => import('@/views/parts/prodPartView'),
      name: 'prodPartView',
      meta: {
        title: 'Production Part View',
        noCache: true,
        activeMenu: '/productionPart/prodPartBrowser'
      },
      hidden: true
    }
  ]
}
export default partsRouter
