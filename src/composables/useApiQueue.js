/**
 * Generic API queue factory.
 *
 * Creates a module-level singleton queue that processes a list of item IDs
 * sequentially, with rate-limit retry and cancellation support.
 *
 * @param {(id: number) => string}             urlFn      — returns the POST URL for a given item ID
 * @param {(id: number, ...args: any[]) => Object} [payloadFn] — returns the POST body (default: empty object)
 * @param {{ delay?: number, retryDelay?: number }} [opts]
 */
import { ref, computed } from 'vue'
import axios from '@nextcloud/axios'

export function createApiQueue(urlFn, payloadFn = () => ({}), opts = {}) {
  const delay = opts.delay ?? 1500
  const retryDelay = opts.retryDelay ?? 10000

  const total = ref(0)
  const done = ref(0)
  const failed = ref(0)
  const finished = ref(true)
  let cancelRequested = false

  const progress = computed(() =>
    total.value === 0 ? 100 : Math.round((done.value / total.value) * 100),
  )

  const running = computed(() => !finished.value && total.value > 0)

  async function start(itemIds, ...args) {
    if (!itemIds?.length) return
    if (!finished.value) return

    total.value = itemIds.length
    done.value = 0
    failed.value = 0
    finished.value = false
    cancelRequested = false

    for (const id of itemIds) {
      if (cancelRequested) break
      const ok = await processOne(id, ...args)
      if (!ok) {
        await sleep(retryDelay)
        await processOne(id, ...args)
      }
      done.value++
      if (!cancelRequested) await sleep(delay)
    }

    finished.value = true
  }

  async function processOne(id, ...args) {
    try {
      await axios.post(urlFn(id), payloadFn(id, ...args))
      return true
    } catch (err) {
      const status = err.response?.status
      if (status === 429) return false
      failed.value++
      return true
    }
  }

  function cancel() {
    cancelRequested = true
  }

  function reset() {
    total.value = 0
    done.value = 0
    failed.value = 0
    finished.value = true
    cancelRequested = false
  }

  function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms))
  }

  return { total, done, failed, finished, progress, running, start, cancel, reset }
}
