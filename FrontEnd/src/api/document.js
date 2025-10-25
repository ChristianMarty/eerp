import eerpApi from '@/api/apiQuery'

class Document {
/* Search *************************************************
  Returns tree of Location
**********************************************************/
  list() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/document',
        method: 'get'
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  category() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/document/category',
        method: 'get',
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

  item(DocumentNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/document/item',
        method: 'get',
        params: { DocumentNumber: DocumentNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  attachment = {
    attachSearchParameters: {
      Table: '',
      DocumentBarcodes: ''
    },
    search(attachSearchParameters = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/document/attachment',
          method: 'get',
          params: attachSearchParameters
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    },
    attachParameters: {
      Table: '',
      DocumentBarcodes: '',
      AttachBarcode: ''
    },
    attach(attachParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/document/attachment',
          data: attachParameters
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

  ingest = {
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/document/ingest/list',
          method: 'get'
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
      DocumentNumber: null,
      IngestName: null,
      Name: '',
      Category: '',
      DocumentDescription: '',
      RevisionDescription: '',
      LinkType: 'Internal'
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
    download(url) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/document/ingest/download',
          data: { Url: url }
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
        Description: ''
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
      },
      purchaseOrderConfirmation(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderConfirmation',
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
      purchaseOrderApproval(purchaseOrderParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/purchaseOrderApproval',
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
      inventoryHistoryCalibrationParameters: {
        FileName: '',
        InventoryNumber: '',
        Description: '',
        Date: '',
        NextDate: ''
      },
      inventoryHistoryCalibration(inventoryHistoryCalibrationParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/document/ingest/template/inventoryHistoryCalibration',
            data: inventoryHistoryCalibrationParameters
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
