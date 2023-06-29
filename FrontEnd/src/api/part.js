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

  searchSupplierPart(SupplierId = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        methood: 'get',
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
}
export default Part
