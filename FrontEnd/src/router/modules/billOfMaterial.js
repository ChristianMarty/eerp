import Layout from '@/layout'

const billOfMaterial = {
  path: '/billOfMaterial',
  component: Layout,
  name: 'parts',
  meta: {
    title: 'Bill of Material',
    icon: 'component'
  },
  children: [
    {
      path: 'billOfMaterialBrowser',
      component: () => import('@/views/billOfMaterial/browser'),
      name: 'billOfMaterialBrowser',
      meta: { title: 'Bill Of Material', icon: 'list' }
    },

    {
      path: 'billOfMaterialView/:BillOfMaterialNumber(.*)',
      component: () => import('@/views/billOfMaterial/view'),
      name: 'billOfMaterialView',
      meta: {
        title: 'Bill of Material View',
        noCache: true,
        activeMenu: '/billOfMaterial/billOfMaterialBrowser'
      },
      hidden: true
    },
    {
      path: 'bomView',
      component: () => import('@/views/billOfMaterial/bomView'),
      name: 'bomView',
      meta: { title: 'Bom View', icon: 'list' }
    }
  ]
}
export default billOfMaterial
