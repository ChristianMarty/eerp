import eerpApi from '@/api/apiQuery'

class ProductionPart {
  search(ProductionPartNumber = null, ManufacturerPartNumberId = null, HideNoManufacturerPart = false) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/productionPart',
        methood: 'get',
        params: {
          ProductionPartNumber: ProductionPartNumber,
          ManufacturerPartNumberId: ManufacturerPartNumberId,
          HideNoManufacturerPart: HideNoManufacturerPart
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

  item(ProductionPartNumber, HideEmptyStock = false) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/productionPart/item',
        methood: 'get',
        params: {
          ProductionPartBarcode: ProductionPartNumber,
          HideEmptyStock: HideEmptyStock
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
