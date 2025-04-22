import eerpApi from '@/api/apiQuery'

class Finance {
  tax = {
    list(Type) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/tax',
          method: 'get',
          params: {
            Type: 'VAT'
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
  currency = {
    list() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/currency',
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
    exchangeRate(CurrencyId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/exchangeRate',
          method: 'get',
          params: {
            CurrencyId: CurrencyId
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

  purchaseOrder = {
    summary(Year) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/purchaseOrder',
          method: 'get',
          params: { Year: Year }
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

  costCenter = {
    list() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/costCenter',
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

    item(CostCenterNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/costCenter/item',
          method: 'get',
          params: { CostCenterNumber: CostCenterNumber }
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

export default Finance
