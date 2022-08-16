import Layout from '@/layout'

const assemblyRouter = {
  path: '/assembly',
  component: Layout,
  meta: {
    title: 'Assembly',
    icon: 'list'
  },
  children: [
   {
      path: 'assemblyBrowser',
      component: () => import('@/views/assembly/browser'),
      name: 'assemblyBrowser',
      meta: { title: 'Assembly', icon: 'list' }
    },
    {
      path: 'item/:AssemblyNo(.*)',
      component: () => import('@/views/assembly/item'),
      name: 'item',
      meta: { title: 'Item', icon: 'el-icon-finished' },
      hidden: true
    }
  ]
}
export default assemblyRouter
