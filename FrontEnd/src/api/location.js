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
          reject(response.error)
        }
      })
    })
  }

  item = {
    get(LocationNumber, Items = true) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/location/item',
          methood: 'get',
          params: { LocationNumber: LocationNumber, Items: Items }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    /* Save *************************************************
    Save Location data
    **********************************************************/
    saveParameters: {
      LocationNumber: null,
      Name: '',
      Title: '',
      Description: '',
      Movable: false,
      ESD: false
    },
    save(saveParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'patch',
          url: '/location/item',
          data: saveParameters
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

  summary(LocationNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/location/summary',
        methood: 'get',
        params: { LocationNumber: LocationNumber }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  transfer(DestinationLocationNumber, TransferList) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/location/transfer',
        data: {
          DestinationLocationNumber: DestinationLocationNumber,
          TransferList: TransferList
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

  bulkTransfer(SourceLocationNumber, DestinationLocationNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/location/bulkTransfer',
        data: { SourceLocationNumber: SourceLocationNumber, DestinationLocationNumber: DestinationLocationNumber }
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

export default Location
