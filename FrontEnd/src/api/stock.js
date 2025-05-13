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
  countingRequest() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock',
        method: 'get',
        params: {
          CountingRequest: true
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
  item = new (class {
    itemCountryOfOriginDataEmpty = {
      Name: '',
      Alpha2Code: '',
      NumericCode: 0
    }
    itemPartWeightUnitOfMeasurementDataEmpty = {
      Unit: '',
      Symbol: ''
    }
    itemSupplierDataEmpty = {
      Name: '',
      PartNumber: '',
      VendorId: 0
    }
    itemPartWeightDataEmpty = {
      SinglePartWeight: null,
      UnitOfMeasurement: Object.assign({}, this.itemPartWeightUnitOfMeasurementDataEmpty)
    }
    itemPartDataEmpty = {
      ManufacturerName: '',
      ManufacturerId: null,
      ManufacturerPartNumber: '',
      ManufacturerPartNumberId: null,
      ManufacturerPartItemId: null,
      SpecificationPartRevisionId: null,
      Weight: Object.assign({}, this.itemPartWeightDataEmpty)
    }
    itemCertaintyEmpty = {
      Factor: 0,
      Rating: 0,
      DaysSinceStocktaking: 0,
      LastStocktakingDate: null
    }
    itemCountingRequestEmpty = {
      Date: null,
      UserInitials: null
    }
    itemQuantityDataEmpty = {
      Quantity: null,
      CreateQuantity: null,
      CreateData: null,
      Certainty: Object.assign({}, this.itemCertaintyEmpty),
      CountingRequest: Object.assign({}, this.itemCountingRequestEmpty)
    }
    itemLocationEmpty = {
      LocationNumber: 0,
      ItemCode: '',
      Path: '',
      HomeName: '',
      HomePath: ''
    }
    itemPurchaseEmpty = {
      PurchaseOrderNumber: 0,
      LineNumber: 0,
      Price: 0,
      Discount: 0,
      CurrencyCode: '',
      OrderReference: '',
      ProductionPartNumber: '',
      Quantity: 0,
      Description: '',
      SupplierId: 0,
      SupplierName: '',
      ItemCode: '',
      PriceAfterDiscount: 0
    }
    itemDataEmpty = {
      ItemCode: '',
      StockNumber: '',
      LotNumber: '',
      Description: '',
      DateCode: '',
      Date: '',
      CountryOfOrigin: Object.assign({}, this.itemCountryOfOriginDataEmpty),
      Supplier: Object.assign({}, this.itemSupplierDataEmpty),
      Purchase: Object.assign({}, this.itemPurchaseEmpty),
      Part: Object.assign({}, this.itemPartDataEmpty),
      Quantity: Object.assign({}, this.itemQuantityDataEmpty),
      Location: Object.assign({}, this.itemLocationEmpty),
      Deleted: false
    }
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
    }
    itemEditDataEmpty = {
      StockCode: '',
      CountryOfOriginNumericCode: null,
      Date: '',
      LotNumber: ''
    }
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
    }
    createParameter = {
      ManufacturerId: null,
      ManufacturerPartNumber: null,
      LocationCode: 'Loc-00000',
      SupplierId: null,
      SupplierPartNumber: null,
      LotNumber: null,
      Quantity: 0,
      Date: null
    }
    createResponse = {
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
    }
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
    }
    countParameter = {
      ItemCode: '',
      Quantity: '',
      Note: ''
    }
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
    }
    requestCounting(ItemCode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/stock/item/requestCounting',
          data: {
            StockCode: ItemCode
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
    }
    history = new (class {
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
      }
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
      updateDateEmpty = {
        EditToken: '',
        Quantity: 0,
        WorkOrderCode: null,
        Note: '',
        Type: ''
      }
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
      }
      removeDataEmpty = {
        ItemCode: '',
        RemoveQuantity: 0,
        WorkOrderNumber: null,
        Note: ''
      }
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
      }
      addDataEmpty = {
        ItemCode: '',
        AddQuantity: 0,
        Note: ''
      }
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
      }
      countDataEmpty = {
        ItemCode: '',
        NewQuantity: 0,
        Note: ''
      }
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
    })();
  })();
}

export default Stock
