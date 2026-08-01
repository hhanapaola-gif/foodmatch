// colores y tamaños de la app, los saque de la app original de flutter

export const customerColors = {
  primary: '#E86A17',
  secondary: '#04B200',
  tabIndicator: '#1981E0',
  background: '#FFFFFF',
  card: '#FFFFFF',
  lightGrayBackground: '#FAFAFA',
  offWhite: '#FCFCFC',
  text: '#002349',
  hint: '#9F9F9F',
  disabled: '#BABFC4',
  border: '#E4E7E9',
  error: '#FF6161',
  white: '#FFFFFF',
  black: '#000000',
  status: {
    pending: '#1D95FF',
    confirmed: '#5B7F12',
    processing: '#BF83FF',
    outForDelivery: '#3CD856',
    ongoing: '#F8BA3F',
    delivered: '#04B200',
    cancelled: '#FF6161',
  },
};

export const customerRadii = {
  small: 5,
  seven: 7,
  default: 10,
  large: 15,
  extraLarge: 20,
};

export const customerSpacing = {
  extraSmall: 5,
  small: 10,
  default: 15,
  large: 20,
  extraLarge: 25,
};

export const customerFontSizes = {
  extraSmall: 10,
  small: 12,
  default: 14,
  large: 16,
  extraLarge: 18,
  overLarge: 24,
};

export const customerFonts = {
  regular: 'Rubik_400Regular',
  medium: 'Rubik_500Medium',
  semiBold: 'Rubik_600SemiBold',
  bold: 'Rubik_700Bold',
};

export const customerTheme = {
  colors: customerColors,
  radii: customerRadii,
  spacing: customerSpacing,
  fontSizes: customerFontSizes,
  fonts: customerFonts,
};

export default customerTheme;
