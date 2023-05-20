import Layout from '@/layout'

const documentRouter = {
  path: '/document',
  component: Layout,
  meta: {
    title: 'Document',
    icon: 'documentation'

  },
  children: [
    {
      path: '',
      component: () => import('@/views/document/browser'),
      name: 'documentBrowser',
      meta: { title: 'Document Browser', icon: 'search' }
    },
    {
      path: 'ingest',
      component: () => import('@/views/document/ingest'),
      name: 'ingestDocument',
      meta: { title: 'Ingest Document', icon: 'documentation', roles: ['document.ingest'] }
    },
    {
      path: 'view/:DocumentNumber(.*)',
      component: () => import('@/views/document/view'),
      name: 'documentView',
      meta: { title: 'Document View', icon: 'list' },
      hidden: true
    }
  ]
}
export default documentRouter
