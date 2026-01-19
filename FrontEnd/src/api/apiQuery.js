import axios from 'axios'
import { Message } from 'element-ui'
import store from '@/store'

// create an axios instance
const eerpApi = axios.create({
  baseURL: process.env.VUE_APP_BLUENOVA_API, // url = base url + request url
  timeout: 15000 // request timeout
})

// request interceptor
eerpApi.interceptors.request.use(
  config => {
    if (store.getters.idempotency) {
      config.headers['Idempotency-Key'] = store.getters.idempotency
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// response interceptor
eerpApi.interceptors.response.use(
  /**
   * If you want to get http information such as headers or status
   * Please return  response => response
  */

  /**
   * Determine the request status by custom code
   * Here is just an example
   * You can also judge the status by HTTP Status Code
   */
  response => {
    const responseData = response.data

    let authenticated = responseData.authenticated
    if (authenticated !== true) {
      authenticated = false
    }

    store.dispatch('user/setAuthenticated', {
      authenticated: authenticated
    })

    /* if (!authenticated) {
      store.dispatch('user/resetToken').then(() => {
        location.reload()
      })
    }//*/

    if (responseData.idempotency) {
      store.dispatch('user/setIdempotency', {
        idempotency: responseData.idempotency
      })
    }

    if (responseData.error) {
      console.error('EERP API Error: ' + responseData.error)
      Message({
        showClose: true,
        message: responseData.error,
        type: 'error',
        duration: 10 * 1000
      })
    }

    return responseData
  }, error => {
    if (error.response.status === 401) {
      store.dispatch('user/resetToken').then(() => {
        location.reload()
      })
    }
  }
)

export default eerpApi
