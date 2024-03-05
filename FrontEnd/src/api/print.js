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

  printer = { // todo: deptocated -> use peripheral list
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
          url: '/renderer',
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

  template = {
    partNote(PrinterId, StockItems, WorkOrderNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/print/partNote',
          data: { PrinterId: PrinterId, Items: StockItems, WorkOrderNumber: WorkOrderNumber }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    partReceipt(PrinterId, StockItems, WorkOrderNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/print/partReceipt',
          data: { PrinterId: PrinterId, Items: StockItems, WorkOrderNumber: WorkOrderNumber }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    assemblyHistoryItem(PrinterId, Data) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/print/assemblyBonPrint',
          data: { PrinterId: PrinterId, Data: Data }
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
