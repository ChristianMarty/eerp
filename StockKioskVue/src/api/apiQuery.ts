import axios from 'axios'
//import { MessageBox } from 'element-plus'
//import store from '@/store'

export class EerpResponse {
  data: any = null;
  error: string|null = null
  loggedin: boolean = false;
  authenticated:boolean = false;
  idempotency:string = "";
}

var idempotency: string = "";

// create an axios instance
const eerpApi = axios.create({
  //baseURL: "http://localhost/api.php", // url = base url + request url
  baseURL: "http://192.168.1.138/api.php", // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 15000, // request timeout,
  headers: {'Idempotency-Key': idempotency}
})



// request interceptor
eerpApi.interceptors.request.use(
  config => {
    // do something before request is sent
    if (idempotency) {
      config.headers['Idempotency-Key'] = idempotency
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
eerpApi.interceptors.response.use(
  response => {
    const res = response.data
    if (res.idempotency) {
      idempotency = res.idempotency
    }
    return res
  }
)

export default eerpApi;
