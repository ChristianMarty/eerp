import eerpApi from '@/api/apiQuery'

class Inventory {
  /* Search *************************************************
  Returns list of Inventory Items
**********************************************************/
  searchParameters = {
    InventoryNumber: null,
    LocationNumber: null,
    CategoryId: null
  }
  searchReturn = []
  searchReturnItem = {
    PicturePath: '',
    InventoryNumber: '',
    InventoryBarcode: '',
    Title: '',
    ManufacturerName: '',
    Type: '',
    SerialNumber: '',
    Status: ''
  }

  search(searchParameters = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/inventory',
        methood: 'get',
        params: {
          InventoryNumber: searchParameters.InventoryNumber,
          LocationNumber: searchParameters.LocationNumber,
          CategoryId: searchParameters.CategoryId
        }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject()
        }
      })
    })
  }

  /* item *************************************************
  Returns data for one specific inventory item
**********************************************************/
  itemReturn = {
    PicturePath: '',
    InventoryNumber: '',
    InventoryBarcode: '',
    Title: '',
    ManufacturerName: '',
    Type: '',
    SerialNumber: '',
    Description: '',
    Note: '',
    MacAddressWired: '',
    MacAddressWireless: '',
    Status: '',
    CategoryId: '',
    LocationNumber: '',
    LocationName: '',
    LocationPath: '',
    HomeLocationName: '',
    HomeLocationPath: '',
    TotalPrice: 0,
    TotalCurrency: '',
    PurchaseInformation: [],
    Documents: [],
    History: []
  }
  item(InventoryNumber) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/inventory/item',
        methood: 'get',
        params: {
          InventoryNumber: InventoryNumber
        }
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject()
        }
      })
    })
  }

  /* create *************************************************
  Creates new inventory item
  Returns Inventory Number of the created item
  **********************************************************/
  createParameters = {
    Title: '',
    ManufacturerName: '',
    Type: '',
    SerialNumber: '',
    LocationNumber: '',
    CategoryId: ''
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/inventory/item',
        data: createParameters
      }).then(response => {
        if (response.error == null) {
          resolve(response.data)
        } else {
          reject(response.error)
        }
      })
    })
  }
  /* categories *************************************************
  Returns tree of inventory categories
  **********************************************************/
  categoriesReturn = []
  categories() {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/inventory/category',
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
  accessory = {
    itemReturn: {
      InventoryNumber: null,
      AccessoryNumber: null,
      Description: '',
      Note: '',
      Labeled: ''
    },
    search(InventoryNumber = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/inventory/accessory/item',
          methood: 'get',
          params: {
            InventoryNumber: InventoryNumber
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    save(Data) {
      let method = null
      if (Data.AccessoryNumber == null) { // Make new enty if no AccessoryNumber is specefied
        method = 'post'
      } else {
        method = 'patch'
      }
      return new Promise((resolve, reject) => {
        eerpApi({
          method: method,
          url: '/inventory/accessory/item',
          data: Data
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

  purchase ={
    search(InventoryNumber = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/inventory/purchase/item',
          methood: 'get',
          params: {
            InventoryNumber: InventoryNumber
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    save(InventoryNumber, PurchaseOrderItems) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'patch',
          url: '/inventory/purchase/item',
          data: { InventoryNumber: InventoryNumber, PurchaseOrderItems: PurchaseOrderItems }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    type() {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/inventory/purchase/type'
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

  history = {

    types() {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/inventory/history/type',
          methood: 'get'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    search(InventoryNumber = null, EditToken = null) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/inventory/history/item',
          methood: 'get',
          params: {
            InventoryNumber: InventoryNumber,
            EditToken: EditToken
          }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },

    itemReturn: {
      InventoryNumber: null,
      Description: '',
      Type: '',
      Date: '',
      NextDate: null,
      EditToken: null
    },
    history(EditToken) {
      return new Promise((resolve, reject) => {
        eerpApi({
          url: '/inventory/history/item',
          methood: 'get',
          params: { EditToken: EditToken }
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    save(Data) {
      let method = null
      if (Data.EditToken == null) { // Make new enty if no EditToken is specefied
        method = 'post'
      } else {
        method = 'patch'
      }
      return new Promise((resolve, reject) => {
        eerpApi({
          method: method,
          url: '/inventory/history/item',
          data: Data
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
}

export default Inventory
