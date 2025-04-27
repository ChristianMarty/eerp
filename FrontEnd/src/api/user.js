import eerpApi from '@/api/apiQuery'

export class User {
  login(username, password) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/user/login',
        method: 'post',
        data: {
          username: username,
          password: password
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
  logout() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/user/logout',
        method: 'post'
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }
  info() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/user/info',
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
