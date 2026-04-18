/**
 * Module-level singleton so market-value fetch state persists across component
 * mount/unmount cycles (e.g. navigating away while the queue runs).
 */
import { generateOcsUrl } from '@nextcloud/router'
import { createApiQueue } from './useApiQueue.js'

const queue = createApiQueue(
  id => generateOcsUrl(`/apps/crate/api/v1/media/${id}/market-value`),
  (_id, currency) => ({ currency }),
)

export function useMarketValueQueue() {
  return queue
}
