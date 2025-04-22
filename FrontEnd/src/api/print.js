import eerpApi from '@/api/apiQuery'

class Print {
  print(RendererId, PrinterId, Data) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/peripheral/printer/print',
        data: {
          RendererId: RendererId,
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

  printer = { // todo: deptocated -> use peripheral list
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/printer',
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
  }

  label = {
    search(Type = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/renderer',
          method: 'get',
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
