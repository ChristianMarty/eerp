
/**
 * @param {}
 * @returns {int}
 */
export default function getNumberOfWeek() {
  const today = new Date()
  const firstDayOfYear = new Date(today.getFullYear(), 0, 1)
  const pastDaysOfYear = (today - firstDayOfYear) / 86400000
  return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7) - 1
}
