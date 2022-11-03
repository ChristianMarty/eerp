import Layout from '@/layout'

const testingRouter = {
  path: '/testing',
  component: Layout,
  name: 'testing',
  meta: {
    title: 'Testing',
    icon: 'component',
    roles: ['testing.view']
  },
  children: [
    {
      path: 'testing',
      component: () => import('@/views/testing/browser'),
      name: 'testingBrowser',
      meta: { title: 'Testing Browser', icon: 'list' }
    },
    {
      path: 'create',
      component: () => import('@/views/testing/create'),
      name: 'testingCreate',
      meta: {
        title: 'Create', icon: 'edit', roles: ['testing.create']
      }
    },
    {
      path: 'item/:TestSystemNumber(.*)',
      component: () => import('@/views/testing/item/'),
      name: 'testingView',
      meta: { title: 'Item', icon: 'el-icon-sold-out' },
      hidden: true
    }

  ]
}
export default testingRouter
