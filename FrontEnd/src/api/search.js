import eerpApi from '@/api/apiQuery'

class Search {
  search(search) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/search',
        method: 'get',
        params: { search: search }
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

export default Search
