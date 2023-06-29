import eerpApi from '@/api/apiQuery'

class ProductionPart {
  search(ProductionPartNumber = null, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/productionPart',
        methood: 'get',
        params: {
          ProductionPartNo: ProductionPartNumber,
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

  item(ProductionPartNumber, ManufacturerPartNumberId = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/productionPart',
        methood: 'get',
        params: {
          ProductionPartNumber: ProductionPartNumber,
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

export default ProductionPart
