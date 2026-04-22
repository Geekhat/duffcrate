<template>
  <NcModal
    :show="show"
    size="small"
    label-id="export-modal-title"
    @close="$emit('close')"
  >
    <div class="export-modal">
      <h2 id="export-modal-title">
        {{ title }}
      </h2>

      <div class="export-field">
        <label class="export-label">Items to export</label>
        <div class="export-radio-group">
          <label class="export-radio-label">
            <input
              v-model="selectedScope"
              type="radio"
              value="owned"
            > Collection (owned)
          </label>
          <label class="export-radio-label">
            <input
              v-model="selectedScope"
              type="radio"
              value="wanted"
            > Wishlist
          </label>
          <label class="export-radio-label">
            <input
              v-model="selectedScope"
              type="radio"
              value="all"
            > All items
          </label>
        </div>
      </div>

      <div class="export-field">
        <label class="export-label">Format</label>
        <div class="export-radio-group">
          <label class="export-radio-label">
            <input
              v-model="format"
              type="radio"
              value="csv"
            > CSV
          </label>
          <label class="export-radio-label">
            <input
              v-model="format"
              type="radio"
              value="xlsx"
            > XLSX (Excel)
          </label>
        </div>
      </div>

      <div class="export-field">
        <label class="export-label">Include</label>
        <label class="export-checkbox-label">
          <input
            v-model="includeEnriched"
            type="checkbox"
          >
          {{ enrichedLabel }}
          <span class="export-hint">({{ enrichedHint }})</span>
        </label>
        <label
          class="export-checkbox-label"
          :class="{ 'export-checkbox-label--disabled': !categoryHasMarket }"
        >
          <input
            v-model="includeMarket"
            type="checkbox"
            :disabled="!categoryHasMarket"
          >
          Market data
          <span class="export-hint">{{ marketHint }}</span>
        </label>
      </div>

      <p
        v-if="error"
        class="export-error"
      >
        {{ error }}
      </p>

      <div class="export-actions">
        <NcButton
          variant="tertiary"
          @click="onCancel"
        >
          Cancel
        </NcButton>
        <NcButton
          variant="primary"
          :disabled="exporting"
          @click="doExport"
        >
          {{ exporting ? 'Preparing download…' : 'Download' }}
        </NcButton>
      </div>
    </div>
  </NcModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { NcModal, NcButton } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'

const props = defineProps({
  show:     { type: Boolean, required: true },
  scope:    { type: String,  default: 'owned' },
  category: { type: String,  default: 'music' },
})

const emit = defineEmits(['close'])

const selectedScope   = ref(props.scope)
const format          = ref('csv')
const includeEnriched = ref(false)
const includeMarket   = ref(false)
const exporting       = ref(false)
const error           = ref('')

// ── category-aware copy ──────────────────────────────────────────────────────
const TITLES = {
  music: 'Export music collection',
  film:  'Export film collection',
  book:  'Export book collection',
  game:  'Export game collection',
  comic: 'Export comic collection',
}
const title = computed(() => TITLES[props.category] ?? 'Export collection')

const ENRICHED_LABELS = {
  music: 'Enriched Discogs data',
  film:  'Enriched TMDB data',
  book:  'Enriched Open Library data',
  game:  'Enriched RAWG data',
  comic: 'Enriched ComicVine data',
}
const enrichedLabel = computed(() => ENRICHED_LABELS[props.category] ?? 'Enriched metadata')

const ENRICHED_HINTS = {
  music: 'genres, country, tracklist, pressing notes, artist bio, members',
  film:  'genres, country, overview',
  book:  'genres, description, author bio',
  game:  'genres, description',
  comic: 'genres, description',
}
const enrichedHint = computed(() => ENRICHED_HINTS[props.category] ?? 'genres and metadata fields')

// Market value availability:
//   - music uses Discogs (single price in display currency)
//   - games / comics use PriceCharting (loose / CIB / new in USD)
//   - films and books have no market-value source
const CATEGORIES_WITH_MARKET = ['music', 'game', 'comic']
const categoryHasMarket = computed(() => CATEGORIES_WITH_MARKET.includes(props.category))

const MARKET_HINTS = {
  music: '(Discogs lowest price, currency, fetched date)',
  game:  '(PriceCharting loose / CIB / new in USD, fetched date)',
  comic: '(PriceCharting loose / CIB / new in USD, fetched date)',
  film:  '— not available for films',
  book:  '— not available for books',
}
const marketHint = computed(() => MARKET_HINTS[props.category] ?? '')

let abortController = null

watch(() => props.show, (open) => {
  if (open) {
    selectedScope.value = props.scope
    error.value = ''
    // Categories without market values shouldn't leak a true checkbox in
    // from a previous open (e.g. user switched from Music to Films).
    if (!categoryHasMarket.value) {
      includeMarket.value = false
    }
  } else if (abortController) {
    // Modal closed mid-export: cancel the in-flight request.
    abortController.abort()
    abortController = null
  }
})

function onCancel() {
  if (abortController) abortController.abort()
  emit('close')
}

async function doExport() {
  exporting.value = true
  error.value = ''
  abortController = new AbortController()
  try {
    const params = new URLSearchParams({
      format:          format.value,
      scope:           selectedScope.value,
      category:        props.category,
      includeEnriched: includeEnriched.value ? '1' : '0',
      includeMarket:   includeMarket.value   ? '1' : '0',
    })
    const url = generateUrl('/apps/crate/export') + '?' + params.toString()
    const res = await axios.get(url, {
      responseType: 'blob',
      timeout: 60000,
      signal: abortController.signal,
    })

    const ext      = format.value === 'xlsx' ? 'xlsx' : 'csv'
    const filename = `crate-export-${new Date().toISOString().slice(0, 10)}.${ext}`

    const blobUrl = URL.createObjectURL(res.data)
    const a       = document.createElement('a')
    a.href        = blobUrl
    a.download    = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(blobUrl)

    emit('close')
  } catch (e) {
    if (axios.isCancel?.(e) || e.name === 'CanceledError' || e.code === 'ERR_CANCELED') {
      // User cancelled — no error message needed.
      return
    }
    console.error('Export failed', e)
    if (e.code === 'ECONNABORTED') {
      showError('Export timed out')
      error.value = 'Export took too long — try exporting without enriched/market data.'
    } else {
      showError('Export failed')
      error.value = 'Export failed — please try again.'
    }
  } finally {
    exporting.value = false
    abortController = null
  }
}
</script>

<style scoped>
.export-modal {
  padding: 24px 28px 28px;
  width: 100%;
  box-sizing: border-box;
}

.export-modal h2 {
  margin: 0 0 20px;
  font-size: 1.25em;
  font-weight: 700;
}

.export-field {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.export-label {
  font-size: 0.875em;
  font-weight: 600;
  color: var(--color-text-maxcontrast);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.export-radio-group {
  display: flex;
  gap: 20px;
}

.export-radio-label,
.export-checkbox-label {
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-size: 0.9em;
  cursor: pointer;
  user-select: none;
}

.export-radio-label input,
.export-checkbox-label input {
  cursor: pointer;
  flex-shrink: 0;
}

.export-checkbox-label--disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.export-checkbox-label--disabled input {
  cursor: not-allowed;
}

.export-hint {
  font-size: 0.82em;
  color: var(--color-text-maxcontrast);
}

.export-error {
  font-size: 0.875em;
  color: var(--color-error);
  margin: 0 0 12px;
}

.export-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid var(--color-border);
}
</style>
