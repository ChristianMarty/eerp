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
      component: () => import('@/views/document/list'),
      name: 'documentBrowser',
      meta: { title: 'Document Browser', icon: 'search', roles: ['document.view'] }
    },
    {
      path: 'ingest',
      component: () => import('@/views/document/ingest'),
      name: 'ingestDocument',
      meta: { title: 'Ingest Document', icon: 'documentation', roles: ['document.ingest'] }
    },
    {
      path: 'item/:DocumentNumber(.*)',
      component: () => import('@/views/document/item'),
      name: 'documentItem',
      meta: { title: 'Document Item', icon: 'list', roles: ['document.view'] },
      hidden: true
    }
  ]
}
export default documentRouter
