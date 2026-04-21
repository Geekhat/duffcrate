/**
 * Build the artwork URL for a media item.
 *
 * Appends the item's `updatedAt` as a cache-busting query string when
 * present so that re-uploaded artwork invalidates cleanly without hammering
 * the cache for unchanged items.
 */
import { generateUrl } from '@nextcloud/router'

/**
 * @param {Object} item - A media item (needs `id` and optionally `updatedAt`).
 * @returns {string} Fully-qualified URL to the artwork endpoint.
 */
export function artworkUrl(item) {
  const v = item.updatedAt ? '?v=' + encodeURIComponent(item.updatedAt) : ''
  return generateUrl('/apps/crate/artwork/' + item.id) + v
}
