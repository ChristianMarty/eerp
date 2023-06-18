import eerpApi from '@/api/apiQuery'

class SupplierPart {
  search(ProductionPartNumber, SupplierId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: {
          ProductionPartNo: ProductionPartNumber,
          SupplierId: SupplierId
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
