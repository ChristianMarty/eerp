import axios from 'axios'
import { MessageBox } from 'element-ui'
import store from '@/store'

// create an axios instance
const eerpApi = axios.create({
  baseURL: process.env.VUE_APP_BLUENOVA_API, // url = base url + request url
  timeout: 15000 // request timeout
})

// request interceptor
eerpApi.interceptors.request.use(
  config => {
    // do something before request is sent
    if (store.getters.idempotency) {
      config.headers['Idempotency-Key'] = store.getters.idempotency
    }
    return config
  },
  error => {
    // do something with request error
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
    const res = response.data

    if (res.idempotency) {
      store.dispatch('user/setIdempotency', {
        idempotency: res.idempotency
      })
    }

    if (res.authenticated === false) {
      // to re-login
      MessageBox.confirm('You have been logged out, you can cancel to stay on this page, or log in again', 'Confirm logout', {
        confirmButtonText: 'Re-Login',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        store.dispatch('user/resetToken').then(() => {
          location.reload()
        })
      })
    }
    // return Promise.reject(new Error(res.message || 'Error'))
    // } else {
    return res
    // }
  }/*,
  error => {
    console.log('err' + error) // for debug
    Message({
      message: error.message,
      type: 'error',
      duration: 5 * 1000
    })
    return Promise.reject(error)
  }*/
)

export default eerpApi
