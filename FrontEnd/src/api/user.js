import requestBN from '@/utils/requestBN'
import eerpApi from '@/api/apiQuery'

export function login(data) {
  return requestBN({
    url: process.env.VUE_APP_BLUENOVA_API + '/user/login',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return requestBN({
    url: process.env.VUE_APP_BLUENOVA_API + '/user/info',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return requestBN({
    url: process.env.VUE_APP_BLUENOVA_API + '/user/logout',
    method: 'post'
  })
}

export class User {
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
