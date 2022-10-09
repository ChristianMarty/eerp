import eerpApi from '@/api/apiQuery'

class Utility {
/* description *************************************************
  Returns description
**********************************************************/
  description(Barcode) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/util/itemDescription',
        methood: 'get',
        params: { Item: Barcode }
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

export default Utility
