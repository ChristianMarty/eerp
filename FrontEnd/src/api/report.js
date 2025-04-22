import eerpApi from '@/api/apiQuery'

class Report {
/* list **************************************************
  Returns list reports
**********************************************************/
  list() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/report',
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
export default Report
