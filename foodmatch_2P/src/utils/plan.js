// Day chips shown in the UI, in the order and abbreviation the backend
// expects (PlanController::order validates against these exact codes).
export const DAYS = [
  { code: 'mon', label: 'Lun' },
  { code: 'tue', label: 'Mar' },
  { code: 'wed', label: 'Mié' },
  { code: 'thu', label: 'Jue' },
  { code: 'fri', label: 'Vie' },
  { code: 'sat', label: 'Sáb' },
  { code: 'sun', label: 'Dom' },
];

// JS Date.getDay() numbering (0 = Sunday ... 6 = Saturday), keyed by the
// same day codes used everywhere else in the app.
export const DAY_CODE_TO_WEEKDAY = { sun: 0, mon: 1, tue: 2, wed: 3, thu: 4, fri: 5, sat: 6 };

export const MEALS = [
  { code: 'breakfast', label: 'Desayuno', priceField: 'breakfast_price' },
  { code: 'lunch', label: 'Almuerzo', priceField: 'lunch_price' },
  { code: 'dinner', label: 'Cena', priceField: 'dinner_price' },
];

// Meals a given plan actually offers (backend allows any of the 3, but a
// plan with no price set for one shouldn't be selectable).
export function availableMeals(plan) {
  return MEALS.filter((m) => Number(plan?.[m.priceField] || 0) > 0);
}

// Business rule from PlanController::order: plans can only start on/after
// the next calendar Monday. Compute it once, client-side, so the UI can
// just display it instead of needing a full date picker.
export function nextMonday() {
  const today = new Date();
  const day = today.getDay(); // 0 = Sunday ... 6 = Saturday
  const daysUntilNextMonday = ((8 - day) % 7) || 7;
  const result = new Date(today);
  result.setDate(today.getDate() + daysUntilNextMonday);
  return result;
}

export function toISODate(date) {
  return date.toISOString().slice(0, 10);
}

export function formatDateEs(date) {
  return date.toLocaleDateString('es-MX', { weekday: 'long', day: 'numeric', month: 'long' });
}
