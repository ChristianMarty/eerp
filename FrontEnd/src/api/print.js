import eerpApi from '@/api/apiQuery'

class Print {
  print(Driver, Language, PrinterId, Data) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/print/print',
        data: {
          Driver: Driver,
          Language: Language,
          PrinterId: PrinterId,
          Data: Data
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

  printer = {
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/printer',
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
  }

  label = {
    search(Type = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/label',
          methood: 'get',
          params: { Tag: Type }
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

export default Print
