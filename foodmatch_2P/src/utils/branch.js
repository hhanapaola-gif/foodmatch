import { SERVER_BASE_URL } from '../config/env';

export const RESTAURANT_PLACEHOLDER = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500&q=60';

// Backend: /branch/list returns bare filenames for image/cover_image (not
// full URLs like Plan does), so they need the storage/branch/ prefix built
// here — see FoodMatch-Backend ConfigController's branch_image_url.
export function branchImageUri(branch) {
  const fileName = branch?.cover_image || branch?.image;
  return fileName ? `${SERVER_BASE_URL}/storage/branch/${fileName}` : RESTAURANT_PLACEHOLDER;
}
