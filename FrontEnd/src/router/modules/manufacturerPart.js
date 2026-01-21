import Layout from '@/layout'

const partsRouter = {
  path: '/manufacturerPart',
  component: Layout,
  name: 'manufacturerPart',
  meta: {
    title: 'Manufacturer Part',
    icon: 'component'
  },
  children: [
    {
      path: 'partSearch',
      component: () => import('@/views/manufacturerPart/partSearch'),
      name: 'partSearch',
      meta: { title: 'Part Search', icon: 'search' }
    },
    {
      path: 'partNumberSearch',
      component: () => import('@/views/manufacturerPart/partNumberSearch'),
      name: 'partNumberSearch',
      meta: { title: 'Part Number Search', icon: 'search' }
    },
    {
      path: 'partSeriesSearch',
      component: () => import('@/views/manufacturerPart/series'),
      name: 'partSeriesSearch',
      meta: { title: 'Part Series', icon: 'search' }
    },
    {
      path: 'createPartSeries',
      component: () => import('@/views/manufacturerPart/series/create'),
      name: 'createPartSeries',
      meta: { title: 'Create Part Series', icon: 'el-icon-plus', roles: ['ManufacturerPartSeries_Create'] }
    },
    {
      path: 'createPartNumber',
      component: () => import('@/views/manufacturerPart/partNumber/create'),
      name: 'createPartNumber',
      meta: { title: 'Create Part Number', icon: 'el-icon-plus', roles: ['ManufacturerPartNumber_Create'] }
    },
    {
      path: 'partNumber/item/:ManufacturerPartNumberId(.*)',
      component: () => import('@/views/manufacturerPart/partNumber/item'),
      name: 'partNumberItem',
      meta: { title: 'Part Number Item', icon: 'list', noCache: true },
      hidden: true
    },
    {
      path: 'series/item/:ManufacturerPartSeriesId(.*)',
      component: () => import('@/views/manufacturerPart/series/item'),
      name: 'partSeriesItem',
      meta: { title: 'Part Series Item', icon: 'list', noCache: true },
      hidden: true
    },
    {
      path: 'item/:ManufacturerPartItemId(.*)',
      component: () => import('@/views/manufacturerPart/item'),
      name: 'partItem',
      meta: { title: 'Part Item', icon: 'list', noCache: true },
      hidden: true
    }
  ]
}
export default partsRouter
