import eerpApi from '@/api/apiQuery'

class UnitOfMeasurement {
  list(Countable = true) {
    return new Promise((resolve, reject) => {
      eerpApi({
        url: '/unitOfMeasurement',
        methood: 'get',
        params: {
          Countable: Countable
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

export default UnitOfMeasurement
