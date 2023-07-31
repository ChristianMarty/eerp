import eerpApi from '@/api/apiQuery'

class SpecificationPart {
  search(SpecificationPartBarcode = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/specificationPart',
        methood: 'get',
        params: {
          SpecificationPartBarcode: SpecificationPartBarcode
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

  item(SpecificationPartBarcode) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/specificationPart/item',
        methood: 'get',
        params: {
          SpecificationPartBarcode: SpecificationPartBarcode
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

  type() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/part/specificationPart/type',
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
    Type: '',
    Title: ''
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/part/specificationPart/item',
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

export default SpecificationPart
