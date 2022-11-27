import eerpApi from '@/api/apiQuery'

class Country {
  /* Country *************************************************
 get Countrys
  **********************************************************/
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/country'
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

export default Country
