import Layout from '@/layout'

const partsMetaRouter = {
  path: '/partMetadata',
  component: Layout,
  meta: {
    title: 'Part Metadata',
    icon: 'component'
  },
  children: [
    {
      path: 'packageBrowser',
      component: () => import('@/views/partMetadata/packageBrowser'),
      meta: { title: 'Packages', icon: 'list' }
    },
    {
      path: 'attributeBrowser',
      component: () => import('@/views/partMetadata/attributeBrowser'),
      meta: { title: 'Attributes', icon: 'list' }
    },
    {
      path: 'classBrowser',
      component: () => import('@/views/partMetadata/classBrowser'),
      meta: { title: 'Part Classes', icon: 'list' }
    }
  ]
}
export default partsMetaRouter
