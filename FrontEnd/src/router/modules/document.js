import Layout from '@/layout'

const documentRouter = {
  path: '/document',
  component: Layout,
  meta: {
    title: 'Documents',
    icon: 'documentation'

  },
  children: [
    {
      path: 'documentBrowser',
      component: () => import('@/views/document/documentBrowser'),
      name: 'documentBrowser',
      meta: { title: 'Document Browser', icon: 'list' }
    },
    {
      path: 'ingestDocument',
      component: () => import('@/views/document/ingest'),
      name: 'ingestDocument',
      meta: { title: 'Ingest Document', icon: 'documentation', roles: ['document.ingest'] }
    }
  ]
}
export default documentRouter
