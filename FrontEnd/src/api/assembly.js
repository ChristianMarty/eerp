import eerpApi from '@/api/apiQuery'

class Assembly {
/* Search *************************************************
  Returns
**********************************************************/

  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/assembly',
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

  item(AssemblyNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/assembly/item',
        methood: 'get',
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
    Description: ''
  }
  create(assemblyCreateParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/assembly ',
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
          methood: 'get',
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
      item(AssemblyHistoryId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            url: '/assembly/unit/history/item',
            methood: 'get',
            params: {
              AssemblyHistoryId: AssemblyHistoryId
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
      historyCreateParameters: {
        AssemblyUnitNumber: '',
        Title: '',
        Description: '',
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
