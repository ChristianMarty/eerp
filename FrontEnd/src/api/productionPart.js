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

  availability(ProductionPartBarcode, AuthorizedOnly = true, Brokers = false) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/part/productionPart/availability',
        params: {
          ProductionPartBarcode: ProductionPartBarcode,
          AuthorizedOnly: AuthorizedOnly,
          Brokers: Brokers
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

  prefix() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/productionPart/prefix',
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

  createParameters = {
    PrefixId: '',
    Description: ''
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/part/productionPart/item',
        data: createParameters
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
