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
      meta: { title: 'Document List', icon: 'search', roles: ['Document_View'] }
    },
    {
      path: 'ingest',
      component: () => import('@/views/document/ingest'),
      meta: { title: 'Ingest Document', icon: 'documentation', roles: ['Document_Ingest_List'] }
    },
    {
      path: 'item/:DocumentNumber(.*)',
      component: () => import('@/views/document/item'),
      meta: { title: 'Document Item', icon: 'list', roles: ['Document_View'] },
      hidden: true
    }
  ]
}
export default documentRouter
