// Fallback icon when a plan category has no real photo uploaded yet —
// picked by keyword match against the category name.
export function categoryIcon(name = '') {
  const n = name.toLowerCase();
  if (n.includes('vegan')) return 'flower-outline';
  if (n.includes('veget')) return 'leaf-outline';
  if (n.includes('prote')) return 'barbell-outline';
  if (n.includes('gluten')) return 'checkmark-circle-outline';
  if (n.includes('famil')) return 'people-outline';
  if (n.includes('calor') || n.includes('carbo')) return 'flame-outline';
  if (n.includes('fit')) return 'fitness-outline';
  return 'nutrition-outline';
}

// Backend: PlanCategory always returns a usable `image` URL, falling back to
// a generic placeholder under /assets/admin/img/ when the category has no
// real photo uploaded — that path pattern is how we tell "no real photo yet"
// apart from an actual upload (which lives under /storage/plan-category/).
export function hasRealCategoryImage(category) {
  return Boolean(category?.image) && !category.image.includes('/assets/admin/img/');
}
