export const getAddonUnitCount = (addon, participants = 1) => {
  const maxParticipants = Number(addon?.maxParticipantsPerUnit || 0)
  const participantCount = Math.max(1, Number(participants) || 1)
  return maxParticipants > 0 ? Math.max(1, Math.ceil(participantCount / maxParticipants)) : 1
}

export const getAddonLineTotal = (addon, participants = 1) => (
  Number(addon?.price || 0) * getAddonUnitCount(addon, participants)
)

export const hydrateAddonForParticipants = (addon, participants = 1) => {
  const quantity = getAddonUnitCount(addon, participants)
  const unitPrice = Number(addon?.price || addon?.unitPrice || 0)
  return {
    ...addon,
    quantity,
    unitPrice,
    totalPrice: unitPrice * quantity,
  }
}
