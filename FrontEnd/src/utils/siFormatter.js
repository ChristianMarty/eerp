/**
 * @param {}
 * @returns {int}
 */
export default function siFormatter(value, unit) {
  if (isNaN(value)) return value

  var output = ''
  var si = ''

  if (value === 0) {
    output = 0
  } else if (value < 0.000000001) {
    output = (value * 1000000000000)
    si = 'p'
  } else if (value < 0.000001) {
    output = (value * 1000000000)
    si = 'n'
  } else if (value < 0.001) {
    output = (value * 1000000)
    si = 'u'
  } else if (value < 1) {
    output = (value * 1000)
    si = 'm'
  } else if (value >= 1000000) {
    output = (value / 1000000)
    si = 'M'
  } else if (value >= 1000) {
    output = (value / 1000)
    si = 'k'
  } else {
    output = value
  }

  const num = Math.round(parseFloat(output) * 100) / 100
  return num.toString() + ' ' + si + unit
}
