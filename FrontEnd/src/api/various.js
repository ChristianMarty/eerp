import eerpApi from '@/api/apiQuery'

class Various {
  WeekNumber() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/various/weekNumber',
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

export default Various
