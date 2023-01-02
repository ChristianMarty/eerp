import axios from 'axios'
import { MessageBox, Message } from 'element-ui'
import store from '@/store'

// create an axios instance
const serviceBN = axios.create({
  baseURL: process.env.VUE_APP_BLUENOVA_API, // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 15000 // request timeout
})

// request interceptor
serviceBN.interceptors.request.use(
  config => {
    // do something before request is sent
    if (store.getters.idempotency) {
      config.headers['Idempotency-Key'] = store.getters.idempotency
    }
    return config
  },
  error => {
    // do something with request error
    console.log(error) // for debug
    return Promise.reject(error)
  }
)

// response interceptor
serviceBN.interceptors.response.use(
  /**
   * If you want to get http information such as headers or status
   * Please return  response => response
  */

  response => {
    const res = response.data

    if (res.idempotency) {
      store.dispatch('user/setIdempotency', {
        idempotency: res.idempotency
      })
    }

    if (res.loggedin === false) {
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
    return res
  }
)

export default serviceBN
