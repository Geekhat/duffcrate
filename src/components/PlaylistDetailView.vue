<template>
  <div class="playlist-detail">
    <!-- Top bar -->
    <div class="pd-topbar">
      <NcButton variant="tertiary" class="pd-back" @click="$emit('back')">← Back</NcButton>
      <div class="pd-topbar-actions">
        <NcButton variant="tertiary" @click="$emit('share', playlist)">Share</NcButton>
        <NcButton variant="error" @click="confirmDelete = true">Delete playlist</NcButton>
      </div>
    </div>

    <!-- Header -->
    <div class="pd-header">
      <div class="pd-cover" :style="coverStyle" />
      <div class="pd-header-info">
        <h2 class="pd-title">{{ playlist.name }}</h2>
        <p v-if="playlist.description" class="pd-desc">{{ playlist.description }}</p>
        <p class="pd-count">{{ playlist.items?.length ?? 0 }} {{ (playlist.items?.length ?? 0) === 1 ? 'album' : 'albums' }}</p>
      </div>
    </div>

    <!-- Items -->
    <div v-if="!playlist.items || playlist.items.length === 0" class="pd-empty">
      <p>No albums in this playlist yet. Open an album and use "Add to playlist" to add it here.</p>
    </div>

    <div v-else class="pd-list">
      <div
        v-for="item in playlist.items"
        :key="item.id"
        class="pd-row"
        @click="$emit('detail', item)"
      >
        <div class="pd-thumb" :style="thumbStyle(item)" />
        <div class="pd-info">
          <span class="pd-item-title">{{ item.title }}</span>
          <span class="pd-item-artist">{{ item.artist }}</span>
          <span class="pd-item-meta">
            <span class="pd-badge">{{ item.format }}</span>
            <template v-if="item.year">&thinsp;{{ item.year }}</template>
          </span>
        </div>
        <div class="pd-actions" @click.stop>
          <NcButton
            variant="tertiary"
            :aria-label="'Remove ' + item.title + ' from playlist'"
            @click="removeItem(item)"
          >
            Remove
          </NcButton>
        </div>
      </div>
    </div>

    <!-- Delete confirm -->
    <NcDialog
      v-if="confirmDelete"
      name="Delete playlist"
      :open="confirmDelete"
      @closing="confirmDelete = false"
    >
      <p>Delete <strong>{{ playlist.name }}</strong>? The albums in it won't be deleted.</p>
      <template #actions>
        <NcButton variant="tertiary" @click="confirmDelete = false">Cancel</NcButton>
        <NcButton variant="error" @click="$emit('delete', playlist)">Delete</NcButton>
      </template>
    </NcDialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { NcButton, NcDialog } from '@nextcloud/vue'
import axios from '@nextcloud/axios'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'

const props = defineProps({
  playlist: { type: Object, required: true },
})

const emit = defineEmits(['back', 'detail', 'delete', 'share', 'updated'])

const confirmDelete = ref(false)

const FORMAT_COLOURS = {
  Vinyl: ['#6b21a8', '#a855f7'],
  CD: ['#1d4ed8', '#60a5fa'],
  SACD: ['#0f766e', '#2dd4bf'],
  Cassette: ['#b45309', '#fbbf24'],
  MiniDisc: ['#0e7490', '#38bdf8'],
}

const coverStyle = computed(() => {
  const first = props.playlist.items?.[0]
  if (first?.artworkPath) {
    const url = generateUrl('/apps/crate/artwork/' + first.id)
    return { backgroundImage: `url(${url})`, backgroundSize: 'cover', backgroundPosition: 'center' }
  }
  return { background: 'linear-gradient(135deg, #374151, #6b7280)' }
})

function thumbStyle(item) {
  if (item.artworkPath) {
    const url = generateUrl('/apps/crate/artwork/' + item.id)
    return { backgroundImage: `url(${url})`, backgroundSize: 'cover', backgroundPosition: 'center' }
  }
  const colours = FORMAT_COLOURS[item.format] ?? ['#374151', '#6b7280']
  return { background: `linear-gradient(135deg, ${colours[0]}, ${colours[1]})` }
}

async function removeItem(item) {
  try {
    const res = await axios.delete(
      generateOcsUrl(`/apps/crate/api/v1/playlists/${props.playlist.id}/items/${item.id}`),
    )
    const updated = res.data.ocs?.data
    if (updated) emit('updated', updated)
  } catch (e) {
    console.error('Failed to remove item from playlist', e)
  }
}
</script>

<style scoped>
.playlist-detail {
  padding: 0 20px 40px;
  max-width: 860px;
}

/* Top bar */
.pd-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: calc(var(--default-clickable-area, 44px) + 8px) 0 16px;
  position: sticky;
  top: 0;
  background: var(--color-main-background);
  z-index: 10;
}

.pd-topbar-actions {
  display: flex;
  gap: 8px;
}

/* Header */
.pd-header {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

@media (max-width: 640px) {
  .pd-header { grid-template-columns: 1fr; }
}

.pd-cover {
  width: 100%;
  aspect-ratio: 1;
  border-radius: var(--border-radius-large);
  background: var(--color-background-dark);
}

.pd-header-info {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 8px;
}

.pd-title {
  margin: 0;
  font-size: 1.8em;
  line-height: 1.2;
}

.pd-desc {
  margin: 0;
  font-size: 0.9em;
  color: var(--color-text-maxcontrast);
}

.pd-count {
  margin: 0;
  font-size: 0.875em;
  color: var(--color-text-maxcontrast);
}

/* Empty */
.pd-empty {
  color: var(--color-text-maxcontrast);
  margin-top: 32px;
}

/* List */
.pd-list {
  display: flex;
  flex-direction: column;
}

.pd-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 8px 12px;
  border-radius: var(--border-radius-large);
  cursor: pointer;
  transition: background 0.1s;
}

.pd-row:hover {
  background: var(--color-background-hover);
}

.pd-row:hover .pd-actions {
  opacity: 1;
}

.pd-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--border-radius);
  flex-shrink: 0;
}

.pd-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.pd-item-title {
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pd-item-artist {
  font-size: 0.875em;
  color: var(--color-text-maxcontrast);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.pd-item-meta {
  font-size: 0.8em;
  color: var(--color-text-maxcontrast);
}

.pd-badge {
  background: var(--color-background-dark);
  padding: 1px 6px;
  border-radius: 10px;
  font-size: 0.85em;
  font-weight: 600;
}

.pd-actions {
  opacity: 0;
  transition: opacity 0.1s;
  flex-shrink: 0;
}
</style>
