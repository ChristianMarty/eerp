import eerpApi from '@/api/apiQuery'

class Part {
  search(ManufacturerId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/part',
        params: { ManufacturerId: ManufacturerId }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }
  searchSupplierPart(SupplierId = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/part/supplierPart',
        params: {
          SupplierId: SupplierId,
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

  class = {
    list(baseClassId, includeParent = false, showHidden = false) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/part/class',
          method: 'get',
          params: {
            ClassId: baseClassId,
            ShowHidden: showHidden,
            IncludeParent: includeParent
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
export default Part
