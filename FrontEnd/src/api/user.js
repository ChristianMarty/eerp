import requestBN from '@/utils/requestBN'

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
