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
      meta: { title: 'Assembly Browser', icon: 'list', roles: ['assembly.view'] }
    },
    {
      path: 'item/:AssemblyNumber(.*)',
      component: () => import('@/views/assembly/item'),
      name: 'assembly',
      meta: { title: 'Assembly', icon: 'el-icon-finished', roles: ['assembly.view'] },
      hidden: true
    },
    {
      path: 'unit/item/:AssemblyUnitNumber(.*)',
      component: () => import('@/views/assembly/unit/item'),
      name: 'assemblyUnit',
      meta: { title: 'Unit', icon: 'el-icon-finished', roles: ['assembly.view'] },
      hidden: true
    },
    {
      path: 'create',
      component: () => import('@/views/assembly/create'),
      name: 'assemblyCreate',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['assembly.create'] }
    }
  ]
}
export default assemblyRouter
