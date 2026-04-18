/**
 * Format a media item's market value as a localised currency string.
 *
 * @param {{ marketValue?: number, marketValueCurrency?: string }} item
 * @returns {string}
 */
export function formatMarketValue(item) {
  if (!item.marketValue) return ''
  try {
    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency: item.marketValueCurrency ?? 'GBP',
      minimumFractionDigits: 2,
    }).format(item.marketValue)
  } catch {
    return `${item.marketValueCurrency ?? ''} ${item.marketValue}`
  }
}
