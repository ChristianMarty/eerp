import eerpApi from '@/api/apiQuery'

class Stock {
  search(HideEmpty = true) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock',
        methood: 'get',
        params: { HideEmpty: this.fliterEmpty }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  item(StockNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock/item',
        methood: 'get',
        params: { StockNo: StockNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  delete(StockId, Note = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'delete',
        url: '/stock/item',
        methood: 'get',
        data: {
          StockNumber: StockId,
          Note: Note
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

  accuracy(StockNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock/accuracy',
        methood: 'get',
        params: { StockNo: StockNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  reservation(StockNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock/reservation',
        methood: 'get',
        params: { StockNo: StockNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }
  purchaseInformation(StockNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/stock/purchaseInformation',
        methood: 'get',
        params: { StockNo: StockNumber }
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

export default Stock
