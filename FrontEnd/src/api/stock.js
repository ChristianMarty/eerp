import eerpApi from '@/api/apiQuery'

class Stock {
  search(HideEmpty = true, StockCode = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock',
        method: 'get',
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
    itemDataEmpty: {
      StockId: '',
      Manufacturer: '',
      ManufacturerPartNumber: '',
      Date: '',
      Quantity: '',
      Location: '',
      Barcode: '',
      Purchase: {},
      Part: {}
    },
    get(ItemCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/item',
          method: 'get',
          params: { StockCode: ItemCode }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    itemEditDataEmpty: {
      StockCode: '',
      CountryOfOriginNumericCode: null,
      Date: '',
      LotNumber: ''
    },
    edit(ItemEditData) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/stock/item',
          method: 'patch',
          data: ItemEditData
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
      Quantity: 0,
      Date: null
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
    countParameter: {
      ItemCode: '',
      Quantity: '',
      Note: ''
    },
    count(countParameter) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/stock/history/item',
          data: {
            StockNumber: countParameter.ItemCode,
            Quantity: countParameter.Quantity,
            Note: countParameter.Note
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
    delete(ItemCode, Note = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'delete',
          url: '/stock/item',
          data: {
            StockCode: ItemCode,
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
    history: {
      list(StockCode) {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/stock/item/history',
            method: 'get',
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
      item(StockHistoryCode) {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/stock/history/item',
            method: 'get',
            params: { StockHistoryCode: StockHistoryCode }
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
}

export default Stock
