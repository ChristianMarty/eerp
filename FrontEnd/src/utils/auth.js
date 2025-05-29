import Cookies from 'js-cookie'

const TokenKey = 'PHPSESSID'

export function getSession() {
  return Cookies.get(TokenKey)
}

export function removeSession() {
  return Cookies.remove(TokenKey)
}
