import eerpApi from '@/api/apiQuery'

class Purchase {
  list(HideClosed = true) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/purchasOrder',
        params: { HideClosed: HideClosed }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  createParameters = {
    SupplierId: '',
    Title: '',
    PurchaseDate: null,
    Description: ''
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/purchasOrder',
        data: createParameters
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  get(VendorId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/purchasOrder',
        params: { VendorId: VendorId }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  line = {
    edit(Lines, PurchaseOrderNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/purchasing/item/edit',
          data: { data: { Action: 'save', Lines: Lines, PoNo: PurchaseOrderNumber }}
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    },
    delete(LineId, PurchaseOrderNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/purchasing/item/edit',
          data: { data: { Action: 'delete', OrderLineId: LineId, PoNo: PurchaseOrderNumber }}
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    }
  }

  item = {
    search(PurchaseOrderNumber = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/item',
          methood: 'get',
          params: {
            PurchaseOrderNo: PurchaseOrderNumber
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    },
    skuSearch(SupplierId, sku) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/item/skuSearch',
          methood: 'get',
          params: { 
            SupplierId: SupplierId, SKU: sku
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    },
    import: {
      load(SupplierId, OrderNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/purchasing/item/import',
            methood: 'get',
            params: {
              SupplierId: SupplierId,
              OrderNumber: OrderNumber
            }
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject()
            }
          })
        })
      },
      save(PurchaseOrderNo, OrderNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/purchasing/item/import',
            params: {
              PurchaseOrderNo: PurchaseOrderNo,
              OrderNumber: OrderNumber
            }
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject()
            }
          })
        })
      }
    }
  }

  orderRequest = {
    list() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/orderRequest',
          methood: 'get'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    }
  }
}

export default Purchase
