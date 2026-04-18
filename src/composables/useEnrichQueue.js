/**
 * Module-level singleton so enrichment state persists across component
 * mount/unmount cycles (e.g. the ImportModal is closed while the queue runs).
 */
import { generateOcsUrl } from '@nextcloud/router'
import { createApiQueue } from './useApiQueue.js'

const queue = createApiQueue(
  id => generateOcsUrl(`/apps/crate/api/v1/media/${id}/enrich`),
)

export function useEnrichQueue() {
  return queue
}
