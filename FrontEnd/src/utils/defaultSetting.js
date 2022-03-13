import store from '@/store'

/**
 * @param
 * @returns {Object}
 * @example
 */
export function defaultSetting() {
  return store.getters.settings.Default
}
