import eerpApi from '@/api/apiQuery'

class SupplierPart {
  search(ProductionPartNumber = null, SupplierId = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/supplierPart',
        method: 'get',
        params: {
          ProductionPartNo: ProductionPartNumber,
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

export default SupplierPart
