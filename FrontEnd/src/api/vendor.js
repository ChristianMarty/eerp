import eerpApi from '@/api/apiQuery'

class Vendor {
  search(Supplier = null, Manufacturer = null, Contractor = null, Carrier = null, Customer = null, IncludeChildren = null) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/vendor',
        method: 'get',
        params: {
          Supplier: Supplier,
          Manufacturer: Manufacturer,
          Contractor: Contractor,
          Carrier: Carrier,
          Customer: Customer,
          IncludeChildren: IncludeChildren
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
    FullName: '',
    ShortName: '',
    AbbreviatedName: '',
    CustomerNumber: '',
    IsSupplier: false,
    IsManufacturer: false,
    IsContractor: false
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

  /* Vendor *************************************************
  Create Vendor
  **********************************************************/
  createParameters = {
    FullName: '',
    IsSupplier: false,
    IsManufacturer: false,
    IsContractor: false,
    IsCarrier: false,
    IsCustomer: false
  }
  create(createParameters) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'post',
        url: '/vendor/item',
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
    },
    delete(AliasId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'delete',
          url: '/vendor/alias/item',
          data: {
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
      CountryNumericCode: 0,
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
    search(VendorId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/contact',
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
      JobTitle: '',
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

  api = {
    informationReturn: {
      Authentication: {
        Authenticated: false,
        AuthenticationUrl: ''
      },
      Capability: {
        OrderImportSupported: false,
        OrderUploadSupported: false,
        SkuSearchSupported: false
      }
    },
    information(SupplierId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/vendor/api/information',
          params: {
            SupplierId: SupplierId
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
  }
}

export default Vendor
