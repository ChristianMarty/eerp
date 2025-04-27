import eerpApi from '@/api/apiQuery'

class ProductionPart {
  search(ProductionPartNumber = null, ManufacturerPartNumberId = null, SpecificationPartRevisionId = null, HideNoManufacturerPart = false) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/productionPart',
        method: 'get',
        params: {
          ProductionPartNumber: ProductionPartNumber,
          ManufacturerPartNumberId: ManufacturerPartNumberId,
          HideNoManufacturerPart: HideNoManufacturerPart,
          SpecificationPartRevisionId: SpecificationPartRevisionId
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
        method: 'get',
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
        method: 'get'
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
  notification = {
    list() {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/productionPart/notification'
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

export default ProductionPart
