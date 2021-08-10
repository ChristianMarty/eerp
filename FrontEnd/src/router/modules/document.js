import Layout from '@/layout'

const documentsRouter = {
  path: '/documents',
  component: Layout,
  meta: {
    title: 'Documents',
    icon: 'documentation'

  },
  children: [
    {
      path: 'documentBrowser',
      component: () => import('@/views/documents/documentBrowser'),
      name: 'documentBrowser',
      meta: { title: 'Document Browser', icon: 'list' }
    },
    {
      path: 'uploadDocument',
      component: () => import('@/views/documents/uploadDocument'),
      name: 'uploadDocument',
      meta: { title: 'Upload Document', icon: 'documentation', roles: ['document.create'] }
    }
  ]
}
export default documentsRouter
