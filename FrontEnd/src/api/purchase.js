import eerpApi from '@/api/apiQuery'

class Purchase {
  list(HideClosed = true, SupplierPartId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/purchaseOrder',
        params: {
          HideClosed: HideClosed,
          SupplierPartId: SupplierPartId
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

  partPurchase(ManufacturerPartNumberId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/purchasing/partPurchase',
        params: {
          ManufacturerPartNumberId: ManufacturerPartNumberId
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
        url: '/purchasing/item',
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
        url: '/purchaseOrder',
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
    save(data) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'patch',
          url: '/purchasing/item',
          data: data
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    updateState(PurchaseOrderNumber, NewState) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'patch',
          url: '/purchasing/item/state',
          params: {
            PurchaseOrderNumber: PurchaseOrderNumber
          },
          data: {
            NewState: NewState
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
    search(PurchaseOrderNumber = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          methood: 'get',
          url: '/purchasing/item',
          params: {
            PurchaseOrderNumber: PurchaseOrderNumber
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
    trackDataItem: {
      ItemCode: '',
      Type: '',
      Description: '',
      CreateQuantity: ''
    },
    track(ReceivalId = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/item/track',
          methood: 'get',
          params: {
            ReceivalId: ReceivalId
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
            reject(response.error)
          }
        })
      })
    },
    receive: {
      get(ReceivalId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            methood: 'get',
            url: 'purchasing/item/received',
            params: {
              ReceivalId: ReceivalId
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
      confirmParameters: {
        ReceivedQuantity: 0,
        ReceivedDate: 0,
        LineId: 0
      },
      confirm(confirmParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/purchasing/item/received',
            data: confirmParameters
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
    meta: {
      get(PurchaseOrderNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            methood: 'get',
            url: '/purchasing/item/meta',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
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
      save(PurchaseOrderNumber, Data) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/purchasing/item/meta',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
            },
            data: Data
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
    additionalChargesLine: {
      listType() {
        return new Promise((resolve, reject) => {
          eerpApi({
            methood: 'get',
            url: '/purchasing/additionalChargeType'
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      emptyLine: {
        LineNumber: 0,
        Type: 'Other',
        Price: 0,
        Quantity: 0,
        VatTaxId: 0,
        Description: ''
      },
      save(PurchaseOrderNumber, Lines) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/purchasing/additionalCharge/edit',
            data: {
              PurchaseOrderNumber: PurchaseOrderNumber,
              Lines: Lines
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
      delete(PurchaseOrderNumber, AdditionalChargeLineId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'delete',
            url: '/purchasing/additionalCharge/edit',
            data: {
              PurchaseOrderNumber: PurchaseOrderNumber,
              AdditionalChargeLineId: AdditionalChargeLineId
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
    },
    line: {
      emptyLine: {
        OrderLineId: 0,
        LineNumber: 0,
        LineType: 'Part',
        QuantityOrdered: 1,
        UnitOfMeasurementId: null,
        Price: 0,
        VatTaxId: null,
        Discount: 0,
        ExpectedReceiptDate: null,
        PartNo: null,
        OrderReference: null,
        SpecificationPartNumber: null,
        SupplierSku: null,
        ManufacturerName: null,
        ManufacturerPartNumber: '',
        StockPart: true,
        Description: '',
        Note: null
      },
      get(LineId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'get',
            url: '/purchasing/item/line',
            params: {
              LineId: LineId
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
      save(PurchaseOrderNumber, LineData) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/purchasing/item/line',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
            },
            data: {
              Lines: LineData
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
      delete(PurchaseOrderNumber, LineId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'delete',
            url: '/purchasing/item/line',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber,
              LineId: LineId
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
      type() {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/purchasing/item/line/type',
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
    },
    match: {
      get(PurchaseOrderNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'get',
            url: '/purchasing/item/match',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
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
      create(PurchaseOrderNumber, LineIds) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/purchasing/item/match',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
            },
            data: {
              Lines: LineIds
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
              reject(response.error)
            }
          })
        })
      },
      save(PurchaseOrderNumber, OrderNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/purchasing/item/import',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber,
              OrderNumber: OrderNumber
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
      upload(PurchaseOrderNumber, Data) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/purchasing/item/upload',
            params: {
              PurchaseOrderNumber: PurchaseOrderNumber
            },
            data: Data
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
            reject(response.error)
          }
        })
      })
    }
  }

  supplierPartNumber = {
    list(VendorId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/supplierPartNumber',
          methood: 'get',
          params: {
            VendorId: VendorId
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

export default Purchase
