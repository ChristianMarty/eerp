import eerpApi from '@/api/apiQuery'

class Location {
/* Search *************************************************
  Returns tree of Location
**********************************************************/
  searchParameters = {
    no: null
  }
  searchReturn = []
  searchReturnItem = {
    Name:	'',
    LocationNumber: '',
    LocationBarcode: '',
    Attributes: {
      EsdSave: false
    },
    Children: [/* array of searchReturnItem */]
  }
  search() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/location',
        methood: 'get'
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject()
        }
      })
    })
  }
}
export default Location
