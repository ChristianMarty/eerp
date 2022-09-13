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
      meta: { title: 'Assembly', icon: 'list', roles: ['assembly.view'] }
    },
    {
      path: 'item/:AssemblyItemNo(.*)',
      component: () => import('@/views/assembly/item'),
      name: 'item',
      meta: { title: 'Item', icon: 'el-icon-finished', roles: ['assembly.view'] },
      hidden: true
    }
  ]
}
export default assemblyRouter
