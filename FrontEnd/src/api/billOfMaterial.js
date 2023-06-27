import eerpApi from '@/api/apiQuery'

class BillOfMaterial {
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/billOfMaterial',
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

export default BillOfMaterial
