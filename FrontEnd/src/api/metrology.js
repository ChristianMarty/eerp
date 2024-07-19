import eerpApi from '@/api/apiQuery'

class Metrology {
  testSystem = {
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/metrology/testSystem',
          methood: 'get'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    testingCreateParameters: {
      Name: '',
      Description: null
    },
    create(testingCreateParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/metrology/testSystem/item',
          data: testingCreateParameters
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    item(TestSystemNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/metrology/testSystem/item',
          methood: 'get',
          params: {
            TestSystemNumber: TestSystemNumber
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
}

export default Metrology
