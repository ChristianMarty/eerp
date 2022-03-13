
export function loadPrinter() {
  const libName = 'ZebraBrowserPrint'

  if (document.getElementById(libName)) return

  const print = document.createElement('script')
  print.setAttribute(
    'src',
    process.env.VUE_APP_BLUENOVA_BASE +
    '/apiFunctions/ZebraPrint/BrowserPrint-3.0.216.min.js'
  )
  print.setAttribute(
    'id', libName

  )
  document.head.appendChild(print)
}

/**
 * @param {string} zplCode
 * @param {Object} data
 * @returns {Boolean}
 */
export function printLabel(zplCode, data) {
  for (const [key, value] of Object.entries(data)) {
    if (value != null) {
      zplCode = zplCode.replaceAll(key, value)
    } else {
      zplCode = zplCode.replaceAll(key, '')
    }
  }

  BrowserPrint.getDefaultDevice(
    'printer',
    function(device) {
      device.send(zplCode, undefined, undefined)
    },
    function() { }
  )

  return true
}
