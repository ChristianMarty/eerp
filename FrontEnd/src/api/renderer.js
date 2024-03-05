import eerpApi from '@/api/apiQuery'

class Renderer {
  list() {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/renderer'
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  item(RendererId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/renderer/item',
        params: {
          RendererId: RendererId
        }
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

export default Renderer
