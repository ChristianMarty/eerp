import Layout from '@/layout'

const partsRouter = {
  path: '/partMetadata',
  component: Layout,
  name: 'packages',
  meta: {
    title: 'Part Metadata',
    icon: 'component'
  },
  children: [
    {
      path: 'packageBrowser',
      component: () => import('@/views/partMetadata/packageBrowser'),
      name: 'packageBrowser',
      meta: { title: 'Packages', icon: 'list' }
    },
    {
      path: 'attributeBrowser',
      component: () => import('@/views/partMetadata/attributeBrowser'),
      name: 'attributeBrowser',
      meta: { title: 'Attributes', icon: 'list' }
    }
  ]
}
export default partsRouter
