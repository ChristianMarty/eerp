import Layout from '@/layout'

const partsRouter = {
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
    }
  ]
}
export default partsRouter
