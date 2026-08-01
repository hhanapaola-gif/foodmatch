import * as Location from 'expo-location';

// Uses the OS's own geocoding service (no Google API key needed) to turn
// the device's current GPS position into a human-readable address.
export async function getCurrentAddress() {
  const { status } = await Location.requestForegroundPermissionsAsync();
  if (status !== 'granted') {
    throw new Error('Necesitamos permiso de ubicación para llenar tu dirección automáticamente.');
  }

  const position = await Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.Balanced });
  const { latitude, longitude } = position.coords;

  const [place] = await Location.reverseGeocodeAsync({ latitude, longitude });
  if (!place) {
    throw new Error('No pudimos identificar tu dirección. Intenta escribirla manualmente.');
  }

  const line = [place.street, place.streetNumber, place.district, place.city, place.region]
    .filter(Boolean)
    .join(', ');

  return { address: line || place.name || `${latitude}, ${longitude}`, latitude, longitude };
}
