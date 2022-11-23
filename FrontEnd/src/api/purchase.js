import eerpApi from '@/api/apiQuery'

class Purchase {
/* Search *************************************************
  Returns list of Purchase Order Items
**********************************************************/

  item = {
    search(PurchaseOrderNumber = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/purchasing/item',
          methood: 'get',
          params: {
            PurchaseOrderNo: PurchaseOrderNumber
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject()
          }
        })
      })
    }
  }
}

export default Purchase
