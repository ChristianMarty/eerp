import defaultSettings from '@/settings'

const title = defaultSettings.title || 'E-ERP'

export default function getPageTitle(pageTitle) {
  if (pageTitle) {
    return `${pageTitle} - ${title}`
  }
  return `${title}`
}
