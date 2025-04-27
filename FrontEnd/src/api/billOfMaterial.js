import eerpApi from '@/api/apiQuery'

class BillOfMaterial {
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial',
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

  save(Bom, RevisionId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial/bom',
        method: 'post',
        data: { Bom: Bom, RevisionId: RevisionId }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  getAnalyzeOptions() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial/analyzeOptions',
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

  analyze(analyzePath, data, quantity, flat = true) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: analyzePath,
        params: { Flat: flat },
        data: { csv: data, BuildQuantity: quantity }
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
    get(BillOfMaterialBarcode) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/item',
          method: 'get',
          params: { BillOfMaterialBarcode: BillOfMaterialBarcode }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    availability(BillOfMaterialRevisionId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/availability',
          method: 'get',
          params: { RevisionId: BillOfMaterialRevisionId }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    placement(BillOfMaterialRevisionId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/placement',
          method: 'get',
          params: { RevisionId: BillOfMaterialRevisionId }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    purchasing(BillOfMaterialRevisionId, Quantity = 1, NoStock = false, KnownSuppliers = true, AuthorizedOnly = true, Brokers = false) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/purchasing',
          method: 'get',
          params: {
            RevisionId: BillOfMaterialRevisionId,
            Quantity: Quantity,
            NoStock: NoStock,
            AuthorizedOnly: AuthorizedOnly,
            Brokers: Brokers,
            KnownSuppliers: KnownSuppliers
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
    analysis(BillOfMaterialRevisionId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/analysis',
          method: 'get',
          params: { RevisionId: BillOfMaterialRevisionId }
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

export default BillOfMaterial
