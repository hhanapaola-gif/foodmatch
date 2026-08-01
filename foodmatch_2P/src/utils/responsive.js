import { useWindowDimensions } from 'react-native';

// para que en pantallas grandes no se vea todo apachurrado en una columna
export function useColumnCount({ base = 2, tablet = 3, desktop = 4, wide = 5 } = {}) {
  const { width } = useWindowDimensions();
  if (width >= 1300) return wide;
  if (width >= 980) return desktop;
  if (width >= 680) return tablet;
  return base;
}

export function useIsWideScreen(breakpoint = 900) {
  const { width } = useWindowDimensions();
  return width >= breakpoint;
}
