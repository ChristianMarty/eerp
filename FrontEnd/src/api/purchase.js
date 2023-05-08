import eerpApi from '@/api/apiQuery'

class Purchase {
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
