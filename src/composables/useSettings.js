/**
 * Module-level singleton so settings state is shared across all components
 * without needing a store.
 */
import { ref, watch } from 'vue'

const KEY_ENRICH_ON_CLICK = 'crate_auto_enrich_click'
const KEY_ENRICH_ON_IMPORT = 'crate_auto_enrich_import'

function readBool(key, defaultValue) {
  const val = localStorage.getItem(key)
  if (val === null) return defaultValue
  return val === 'true'
}

const autoEnrichOnClick = ref(readBool(KEY_ENRICH_ON_CLICK, true))
const autoEnrichOnImport = ref(readBool(KEY_ENRICH_ON_IMPORT, true))

watch(autoEnrichOnClick, v => localStorage.setItem(KEY_ENRICH_ON_CLICK, String(v)))
watch(autoEnrichOnImport, v => localStorage.setItem(KEY_ENRICH_ON_IMPORT, String(v)))

export function useSettings() {
  return { autoEnrichOnClick, autoEnrichOnImport }
}
