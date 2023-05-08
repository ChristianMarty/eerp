import eerpApi from '@/api/apiQuery'

class Part {
  search(ManufacturerId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        methood: 'get',
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

  searchSupplierPart(SupplierId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        methood: 'get',
        url: '/supplier/supplierPart',
        params: { SupplierId: SupplierId }
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
export default Part
