import eerpApi from '@/api/apiQuery'

class Renderer {
  Dataset = {
    Stock: 1,
    AssemblyUnitHistory: 2,
    StockHistory: 3,
    PurchaseOrder: 4,
    InventoryItem: 5,
    LocationItem: 6,
    StockReceipt: 7
  }

  list(Flat = true, DatasetId = 0) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/renderer',
        params: {
          Flat: Flat,
          DatasetId: DatasetId
        }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  item(RendererId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/renderer/item',
        params: {
          RendererId: RendererId
        }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }
}

export default Renderer
