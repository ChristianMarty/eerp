import eerpApi from '@/api/apiQuery'

class Vendor {
  /* Vendor *************************************************
 Create Vendor
  **********************************************************/
  create(VendorName) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/vendor',
        data: {
          Name: VendorName
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

  search(Supplier = null, Manufacturer = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/vendor',
        methood: 'get',
        params: {
          Supplier: Supplier,
          Manufacturer: Manufacturer
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

  item(VendorId) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/vendor/item',
        params: {
          VendorId: VendorId
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

  /* Save *************************************************
  Save vendor data
  **********************************************************/
  saveParameters = {
    VendorId: '',
    ParentId: null,
    Name: '',
    ShortName: '',
    CustomerNumber: '',
    IsSupplier: '',
    IsManufacturer: ''
  }
  save(saveParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'patch',
        url: '/vendor/item',
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

  alias = {
    searchReturn: {
      AliasId: null,
      VendorId: null,
      Name: '',
      Note: ''
    },
    search(AliasId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/alias/item',
          params: {
            AliasId: AliasId
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
    saveParameters: {
      AliasId: null,
      VendorId: null,
      Name: '',
      Note: ''
    },
    save(saveParameters) {
      let method = null
      if (saveParameters.AliasId == null) { // Make new enty if no AliasId is specefied
        method = 'post'
      } else {
        method = 'patch'
      }
      return new Promise((resolve, reject) => {
        eerpApi({
          method: method,
          url: '/vendor/alias/item',
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

  address = {
    search(VendorId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/address',
          params: {
            VendorId: VendorId
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
      Id: null,
      CountryId: 0,
      CountryCode: '',
      CountryName: '',
      PostalCode: '',
      City: '',
      Street: '',
      VatTaxNumber: '',
      CustomsAccountNumber: ''
    },
    item(AddressId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/address/item',
          params: {
            AddressId: AddressId
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
    saveParameters: {
      AddressId: null,
      VendorId: null,
      CountryId: 0,
      PostalCode: '',
      City: '',
      Street: '',
      VatTaxNumber: '',
      CustomsAccountNumber: ''
    },
    save(saveParameters) {
      let method = null
      if (saveParameters.AddressId == null) { // Make new enty if no AddressId is specefied
        method = 'post'
      } else {
        method = 'patch'
      }
      return new Promise((resolve, reject) => {
        eerpApi({
          method: method,
          url: '/vendor/address/item',
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
  contact = {
    language() {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/contact/language'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    gender() {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/contact/gender'
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
      Id: null,
      CountryId: 0,
      CountryCode: '',
      CountryName: '',
      PostalCode: '',
      City: '',
      Street: '',
      VatTaxNumber: '',
      CustomsAccountNumber: ''
    },
    item(ContactId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/contact/item',
          params: {
            ContactId: ContactId
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
    saveParameters: {
      ContactId: null,
      VendorId: 0,
      AddressId: 0,
      Gender: '',
      FirstName: '',
      LastName: '',
      Language: '',
      Phone: '',
      EMail: ''
    },
    save(saveParameters) {
      let method = null
      if (saveParameters.ContactId == null) { // Make new enty if no ContactId is specefied
        method = 'post'
      } else {
        method = 'patch'
      }
      return new Promise((resolve, reject) => {
        eerpApi({
          method: method,
          url: '/vendor/contact/item',
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
}

export default Vendor
