import eerpApi from '@/api/apiQuery'

class BillOfMaterial {
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial',
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

  getAnalyzeOptions() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial/analyzeOptions',
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
          methood: 'get',
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
          methood: 'get',
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
    analysis(BillOfMaterialRevisionId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/billOfMaterial/analysis',
          methood: 'get',
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
