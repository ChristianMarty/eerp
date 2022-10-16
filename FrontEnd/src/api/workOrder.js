import eerpApi from '@/api/apiQuery'

class WorkOrder {
/* Search *************************************************
  Returns tree of Location
**********************************************************/
  search(Status) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/workOrder',
        methood: 'get',
        params: { Status: Status }
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

export default WorkOrder
