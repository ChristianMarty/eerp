
/**
 * @param {}
 * @returns {int}
 */
export default function parseDaycode(dayCode) {
  var isnum = /^\d+$/.test(dayCode)
  if (dayCode.length !== 4) isnum = false

  if (isnum == false) {
    this.$message({
      showClose: true,
      message: 'Daycode parser error',
      duration: 0,
      type: 'error'
    })
  } else {
    var year = dayCode.substr(0, 2)
    var week = dayCode.substr(2, 2)

    if (parseInt(year, 10) < 80) year = '20' + year
    else year = '19' + year

    var d = 1 + (week - 1) * 7

    return new Date(year, 0, d).toISOString().split('T')[0]
  }
}
