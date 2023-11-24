import eerpApi from '@/api/apiQuery'

class Metrology {
/* Search *************************************************

**********************************************************/
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/metrology',
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

  testingCreateParameters = {
    Name: '',
    Description: null
  }
  create(testingCreateParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/metrology ',
        data: testingCreateParameters
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  system = {
    item(TestSystemNumber, TestDate) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/metrology/item',
          methood: 'get',
          params: {
            TestSystemNumber: TestSystemNumber,
            TestDate: TestDate
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
