/**
 * Composable that returns a computed artwork background style for a media item.
 *
 * Handles both local/remote artwork URLs and format-coloured gradient fallbacks.
 */
import { computed } from 'vue'
import { FORMAT_COLOURS } from '../utils/formatColours.js'
import { artworkUrl } from '../utils/artworkUrl.js'

function styleForItem(item) {
  if (item?.artworkPath) {
    return {
      backgroundImage: `url(${artworkUrl(item)})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
    }
  }
  const colours = FORMAT_COLOURS[item?.format] ?? ['#374151', '#6b7280']
  return { background: `linear-gradient(135deg, ${colours[0]}, ${colours[1]})` }
}

/**
 * @param {import('vue').Ref<Object>|import('vue').ComputedRef<Object>} itemRef
 *   Reactive reference to a media item (must have id, artworkPath, updatedAt, format).
 * @returns {import('vue').ComputedRef<Object>} CSS style object for background display.
 */
export function useArtworkStyle(itemRef) {
  return computed(() => {
    const item = itemRef.value
    return item ? styleForItem(item) : {}
  })
}

/**
 * Non-reactive helper for use in plain functions (e.g. thumbStyle in lists).
 *
 * @param {Object} item - A media item object.
 * @returns {Object} CSS style object.
 */
export function artworkStyleFor(item) {
  return styleForItem(item)
}
