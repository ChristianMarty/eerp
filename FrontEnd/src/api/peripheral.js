import eerpApi from '@/api/apiQuery'

class Peripheral {
  Type = {
    Printer: 'printer',
    Scale: 'scale'
  }

  /**
   * Search for peripheral
   * @param {Type} Type
   */
  search(Type) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/peripheral',
        params: {
          Type: Type
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

  scale = {
    /**
     * read weight from scale
     * @param {int} PeripheralId
     */
    read(PeripheralId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/peripheral/scale/read',
          methood: 'get',
          params: {
            PeripheralId: PeripheralId
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

export default Peripheral
