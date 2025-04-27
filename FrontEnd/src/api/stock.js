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
      ItemCode: '',
      StockNumber: '',
      LotNumber: '',
      Description: '',
      DateCode: '',
      Date: '',
      CountryOfOrigin: {},
      Supplier: {},
      Purchase: {},
      Part: {},
      Quantity: {},
      Location: {},
      Deleted: false
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
      },
      updateDateEmpty: {
        EditToken: '',
        Quantity: 0,
        WorkOrderCode: null,
        Note: '',
        Type: ''
      },
      update(updateData) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/stock/history/item',
            data: {
              EditToken: updateData.EditToken,
              Quantity: updateData.Quantity,
              WorkOrderNumber: updateData.WorkOrderCode,
              Note: updateData.Note,
              Type: updateData.Type
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
      removeDataEmpty: {
        ItemCode: '',
        RemoveQuantity: 0,
        WorkOrderNumber: null,
        Note: ''
      },
      remove(removeData) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/stock/history/item',
            data: {
              StockNumber: removeData.ItemCode,
              RemoveQuantity: removeData.RemoveQuantity,
              WorkOrderNumber: removeData.WorkOrderNumber,
              Note: removeData.Note
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
      addDataEmpty: {
        ItemCode: '',
        AddQuantity: 0,
        Note: ''
      },
      add(addData) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/stock/history/item',
            data: {
              StockNumber: addData.ItemCode,
              AddQuantity: addData.AddQuantity,
              Note: addData.Note
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
      countDataEmpty: {
        ItemCode: '',
        NewQuantity: 0,
        Note: ''
      },
      count(addData) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/stock/history/item',
            data: {
              StockNumber: addData.ItemCode,
              Quantity: addData.NewQuantity,
              Note: addData.Note
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
  }
}

export default Stock
