import eerpApi from '@/api/apiQuery'

class Stock {
  search(HideEmpty = true, StockCode = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock',
        methood: 'get',
        params: {
          HideEmpty: HideEmpty,
          StockCode: StockCode,
          ManufacturerPartNumberId
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

  bulkRemove(StockItems, WorkOrderNumber = null) {
    const data = {
      Items: StockItems,
      WorkOrderNumber: WorkOrderNumber
    }

    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/stock/history/bulkRemove',
        data: data
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  item = {
    get(StockCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/item',
          methood: 'get',
          params: { StockCode: StockCode }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    createParameter: {
      ManufacturerId: null,
      ManufacturerPartNumber: null,
      LocationCode: 'Loc-00000',
      SupplierId: null,
      SupplierPartNumber: null,
      LotNumber: null,
      OrderReference: null,
      Quantity: 0,
      Date: null
    },
    create(CreateParameter) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/stock/item',
          data: CreateParameter
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    createResponse: {
      StockId: '',
      ManufacturerName: '',
      Supplier: '',
      ManufacturerPartNumber: '',
      Date: '',
      Quantity: '',
      Location: '',
      Barcode: '',
      SupplierName: '',
      SupplierPartNumber: ''
    },
    delete(StockId, Note = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'delete',
          url: '/stock/item',
          methood: 'get',
          data: {
            StockCode: StockId,
            Note: Note
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    accuracy(StockCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/accuracy',
          methood: 'get',
          params: { StockCode: StockCode }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    reservation(StockCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/reservation',
          methood: 'get',
          params: { StockCode: StockCode }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    purchaseInformation(StockCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/purchaseInformation',
          methood: 'get',
          params: { StockCode: StockCode }
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
}

export default Stock
