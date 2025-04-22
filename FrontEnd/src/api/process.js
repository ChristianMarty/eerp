import eerpApi from '@/api/apiQuery'

class Process {
/* list **************************************************
  Returns list processes
**********************************************************/
  list() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/process',
        method: 'get'
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
export default Process
