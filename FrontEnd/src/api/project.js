import eerpApi from '@/api/apiQuery'

class Project {
/* Search *************************************************
  Returns list of Purchase Order Items
**********************************************************/
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/project',
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

  item = {
    get(ProjectNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/project/item',
          methood: 'get',
          params: { ProjectNumber: ProjectNumber }
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
export default Project
