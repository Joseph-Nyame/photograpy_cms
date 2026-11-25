export function money(value, currency = 'GHS') {
  const amount = Number(value ?? 0)
  const fractionDigits = Number.isInteger(amount) ? 0 : 2

  try {
    return new Intl.NumberFormat(undefined, {
      style: 'currency',
      currency,
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    }).format(amount)
  } catch {
    return `${currency} ${amount.toLocaleString()}`
  }
}
