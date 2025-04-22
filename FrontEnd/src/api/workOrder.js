import eerpApi from '@/api/apiQuery'

class WorkOrder {
  createParameters = {
    Name: '',
    ProjectCode: '',
    Quantity: ''
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/workOrder/item',
        data: createParameters
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  search(Status = null, HideClosed = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/workOrder',
        method: 'get',
        params: { Status: Status, HideClosed: HideClosed }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  item(WorkOrderNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/workOrder/item',
        params: { WorkOrderNumber: WorkOrderNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  status() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/workOrder/status',
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

  updateStatus(WorkOrderNumber, status) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'PATCH',
        url: '/workOrder/item',
        data: { WorkOrderNumber: WorkOrderNumber, Status: status }
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
