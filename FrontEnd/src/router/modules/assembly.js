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
      path: '',
      component: () => import('@/views/assembly/list'),
      name: 'assemblyList',
      meta: { title: 'Assembly List', icon: 'search', roles: ['Assembly_List'] }
    },
    {
      path: 'item/:AssemblyNumber(.*)',
      component: () => import('@/views/assembly/item'),
      name: 'assembly',
      meta: { title: 'Assembly', icon: 'el-icon-finished', roles: ['Assembly_View'] },
      hidden: true
    },
    {
      path: 'unit/item/:AssemblyUnitNumber(.*)',
      component: () => import('@/views/assembly/unit/item'),
      name: 'assemblyUnit',
      meta: { title: 'Unit', icon: 'el-icon-finished', roles: ['Assembly_Unit_View'] },
      hidden: true
    },
    {
      path: 'create',
      component: () => import('@/views/assembly/create'),
      name: 'assemblyCreate',
      meta: { title: 'Create', icon: 'el-icon-plus', roles: ['Assembly_Create'] }
    }
  ]
}
export default assemblyRouter
