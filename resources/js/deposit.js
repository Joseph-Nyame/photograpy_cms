export function depositFor(total, ratePercent = 30) {
  const amount = Number(total)
  const rate = Number(ratePercent)

  if (!Number.isFinite(amount) || !Number.isFinite(rate) || amount <= 0) return 0

  return Math.round((amount * rate / 100) * 100) / 100
}

export function lowestDeposit(services, ratePercent = 30) {
  const values = (services ?? [])
    .map(service => Number(service?.deposit ?? depositFor(service?.price, ratePercent)))
    .filter(value => Number.isFinite(value) && value > 0)

  return values.length ? Math.min(...values) : 0
}
