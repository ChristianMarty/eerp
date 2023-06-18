import eerpApi from '@/api/apiQuery'

class Finance {
  tax = {
    list(Type) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/tax',
          methood: 'get',
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
    exchangeRate(CurrencyId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/exchangeRate',
          methood: 'get',
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
          methood: 'get',
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

    item(CostCenterNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/finance/costCenter/item',
          methood: 'get',
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
