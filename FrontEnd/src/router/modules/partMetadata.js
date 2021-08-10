import Layout from '@/layout'

const partsMetaRouter = {
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
    },
    {
      path: 'classBrowser',
      component: () => import('@/views/partMetadata/classBrowser'),
      name: 'classBrowser',
      meta: { title: 'Part Classes', icon: 'list' }
    },
    {
      path: 'supplierBrowser',
      component: () => import('@/views/partMetadata/supplierBrowser'),
      name: 'supplierBrowser',
      meta: { title: 'Suppliers', icon: 'list' }
    }
  ]
}
export default partsMetaRouter
