import eerpApi from '@/api/apiQuery'

class Assembly {
/* Search *************************************************
  Returns
**********************************************************/

  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/assembly',
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

  item(AssemblyNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/assembly/item',
        method: 'get',
        params: {
          AssemblyNumber: AssemblyNumber
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

  assemblyCreateParameters = {
    Name: '',
    Description: null
  }
  create(assemblyCreateParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/assembly/item',
        data: assemblyCreateParameters
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  unit = {
    item(AssemblyUnitNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/assembly/unit/item',
          method: 'get',
          params: {
            AssemblyUnitNumber: AssemblyUnitNumber
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    assemblyCreateParameters: {
      SerialNumber: '',
      AssemblyNumber: '',
      WorkOrderNumber: ''
    },
    create(assemblyCreateParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/assembly/unit/item',
          data: assemblyCreateParameters
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    history: {
      item(AssemblyUnitHistoryNumber) {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/assembly/unit/history/item',
            method: 'get',
            params: {
              AssemblyUnitHistoryNumber: AssemblyUnitHistoryNumber
            }
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      types() {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/assembly/unit/history/type',
            method: 'get'
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      historyCreateParameters: {
        AssemblyUnitNumber: '',
        Title: '',
        Description: '',
        Date: '',
        Type: '',
        ShippingClearance: false,
        ShippingProhibited: false,
        Data: ''
      },
      create(historyCreateParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/assembly/unit/history/item',
            data: historyCreateParameters
          }).then(response => {
            if (response.error == null) {
              resolve(response.data)
            } else {
              reject(response.error)
            }
          })
        })
      },
      historyUpdateParameters: {
        EditToken: '',
        Title: '',
        Description: '',
        Date: '',
        Type: '',
        ShippingClearance: false,
        ShippingProhibited: false,
        Data: ''
      },
      update(historyUpdateParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/assembly/unit/history/item',
            data: historyUpdateParameters
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
}

export default Assembly
