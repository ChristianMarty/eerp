import eerpApi from '@/api/apiQuery'

class Document {
/* Search *************************************************
  Returns tree of Location
**********************************************************/
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/document',
        methood: 'get'
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  types() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/document/type',
        methood: 'get',
        params: { documents: '0' }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  ingest = {
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/document/ingest/list',
          methood: 'get'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    ingestParameters: {
      FileName: '',
      Name: '',
      Description: '',
      Type: '',
      Note: ''
    },
    ingest(ingestParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/document/ingest/item',
          data: ingestParameters
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    template: {
      purchaseOrderParameters: {
        FileName: '',
        PurchaseOrderNumber: '',
        Note: ''
      },
      purchaseOrderDeliveryNote(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderDeliveryNote',
            data: purchaseOrderParameters
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      purchaseOrderQuote(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderQuote',
            data: purchaseOrderParameters
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      purchaseOrderInvoice(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderInvoice',
            data: purchaseOrderParameters
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      purchaseOrderReceipt(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderReceipt',
            data: purchaseOrderParameters
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      }
    },

    delete(ingestParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'delete',
          url: '/document/ingest/item',
          data: ingestParameters
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

export default Document
