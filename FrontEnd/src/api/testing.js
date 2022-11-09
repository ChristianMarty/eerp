import eerpApi from '@/api/apiQuery'

class Testing {
/* Search *************************************************

**********************************************************/
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/testing',
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
    Description: ''
  }
  create(testingCreateParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/testing ',
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
          url: '/testing/item',
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

export default Testing
