import eerpApi from '@/api/apiQuery'
import ProductionPart from './productionPart'

class ManufacturerPart {
  searchParameters = {
    VendorId: null,
    ManufacturerPartNumber: null,
    ClassId: null
  }
  search(searchParameters = null, flat = false) {
    return new Promise((resolve, reject) => {
      eerpApi({
        method: 'get',
        url: '/part/manufacturerPart',
        params: {
          VendorId: searchParameters.VendorId,
          ManufacturerPartNumber: searchParameters.ManufacturerPartNumber,
          ClassId: searchParameters.ClassId
        }
      }).then(response => {
        if (response.error == null) {
          const data = response.data
          if (flat) {
            data.forEach(item => {
              item.PartData.forEach(element => {
                Object.defineProperty(item, element.Name, {
                  value: this.getAttributeValue(element.Value),
                  writable: true,
                  enumerable: true,
                  configurable: true
                })
              })
            })
          }
          resolve(data)
        } else {
          reject(response.error)
        }
      })
    })
  }

  getAttributeValue(value) { // TODO: remove this as function
    if (value === null) return ''

    if (typeof value === 'object') {
      var out = value.Minimum
      if (value.Typical) out += ' - ' + value.Typical
      out += ' - ' + value.Maximum
      return out
    } else {
      return value
    }
  }

  item = {
    get(ManufacturerPartItemId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/item',
          params: {
            ManufacturerPartItemId: ManufacturerPartItemId
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
    characteristics: {
      get(ManufacturerPartItemId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'get',
            url: '/part/manufacturerPart/item/characteristics',
            params: {
              ManufacturerPartItemId: ManufacturerPartItemId
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
      save(ManufacturerPartItemId, ClassId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'patch',
            url: '/part/manufacturerPart/item/characteristics',
            data: {
              ManufacturerPartItemId: ManufacturerPartItemId,
              ClassId: ClassId
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

  attribute = {
    search(ClassId = 0, Children = false, Parents = true) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/attribute',
          params: {
            classId: ClassId,
            children: Children,
            parents: Parents
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

  class = {
    getFilterOption(ClassId = 0) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/filterOption',
          params: { ClassId: ClassId }
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

  series = {
    search() {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/series'
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    item(ManufacturerPartSeriesId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/series/item',
          params: {
            ManufacturerPartSeriesId: ManufacturerPartSeriesId
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
    seriesCreateParameters: {
      VendorId: 0,
      Title: '',
      Description: ''
    },
    create(seriesCreateParameters) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/part/manufacturerPart/series/item ',
          data: seriesCreateParameters
        }).then(response => {
          if (response.error == null) {
            resolve(response.data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    template: {
      get(ManufacturerPartSeriesId) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'get',
            url: '/part/manufacturerPart/series/template',
            params: {
              ManufacturerPartSeriesId: ManufacturerPartSeriesId
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
      seriesTemplateParameters: {
        ManufacturerPartSeriesId: 0,
        SeriesNameMatch: '',
        NumberTemplate: '',
        Parameter: ''
      },
      save(seriesTemplateParameters) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/part/manufacturerPart/series/template',
            data: seriesTemplateParameters
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

  PartNumber = {
    searchParameters: {
      VendorId: null,
      ManufacturerPartNumber: null
    },
    search(searchParameters = null) {
      if (!searchParameters.ManufacturerPartNumber) searchParameters.ManufacturerPartNumber = null

      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/partNumber',
          params: {
            VendorId: searchParameters.VendorId,
            ManufacturerPartNumber: searchParameters.ManufacturerPartNumber
          }
        }).then(response => {
          if (response.error == null) {
            const data = response.data
            resolve(data)
          } else {
            reject(response.error)
          }
        })
      })
    },
    get(PartNumberId) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/partNumber/item',
          params: {
            PartNumberId: PartNumberId
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
    create(VendorId, PartNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'post',
          url: '/part/manufacturerPart/partNumber/item',
          data: {
            VendorId: VendorId,
            PartNumber: PartNumber
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
    saveWeight(PartNumberId, SinglePartWeight) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'patch',
          url: '/part/manufacturerPart/partNumber/weight',
          data: {
            PartNumberId: PartNumberId,
            SinglePartWeight: SinglePartWeight
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
    availability(ManufacturerPartNumberId, AuthorizedOnly = true, Brokers = false) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/partNumber/availability',
          params: {
            ManufacturerPartNumberId: ManufacturerPartNumberId,
            AuthorizedOnly: AuthorizedOnly,
            Brokers: Brokers
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
    analyze(VendorId, PartNumber) {
      return new Promise((resolve, reject) => {
        eerpApi({
          method: 'get',
          url: '/part/manufacturerPart/partNumber/analyze',
          params: {
            VendorId: VendorId,
            PartNumber: PartNumber
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
    productionPart: {
      saveMapping(ManufacturerPartNumberId, productionPart) {
        return new Promise((resolve, reject) => {
          eerpApi({
            method: 'post',
            url: '/part/manufacturerPart/partNumber/productionPart',
            data: {
              ManufacturerPartNumberId: ManufacturerPartNumberId,
              ProductionPartList: productionPart
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
}
export default ManufacturerPart
