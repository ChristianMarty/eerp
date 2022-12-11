import eerpApi from '@/api/apiQuery'

class Finance {
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
}

export default Finance
