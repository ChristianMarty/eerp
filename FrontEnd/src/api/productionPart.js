import eerpApi from '@/api/apiQuery'

class ProductionPart {
  item(ProductionPartNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/productionPart',
        methood: 'get',
        params: {
          ProductionPartNumber: ProductionPartNumber
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
