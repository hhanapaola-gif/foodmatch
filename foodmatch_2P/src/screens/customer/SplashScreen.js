import React, { useEffect } from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import { useAuth } from '../../context/customer/AuthContext';

const logo = require('../../../assets/logo.png');
const MIN_DISPLAY_MS = 900;

export default function SplashScreen({ navigation }) {
  const { isBooting, isAuthenticated } = useAuth();

  useEffect(() => {
    const start = Date.now();
    let cancelled = false;

    const tryNavigate = () => {
      if (cancelled || isBooting) return;
      const elapsed = Date.now() - start;
      const wait = Math.max(0, MIN_DISPLAY_MS - elapsed);
      setTimeout(() => {
        if (cancelled) return;
        navigation.replace(isAuthenticated ? 'Dashboard' : 'Onboarding');
      }, wait);
    };

    tryNavigate();
    return () => {
      cancelled = true;
    };
  }, [isBooting, isAuthenticated, navigation]);

  return (
    <View style={styles.container}>
      <Image source={logo} style={styles.logo} resizeMode="contain" />
      <Text style={styles.title}>FoodMatch</Text>
      <Text style={styles.subtitle}>Planes alimenticios por suscripción</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: customerColors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo: {
    width: 100,
    height: 100,
  },
  title: {
    marginTop: 16,
    color: '#fff',
    fontFamily: customerFonts.bold,
    fontSize: customerFontSizes.overLarge + 6,
  },
  subtitle: {
    marginTop: 8,
    color: '#fff',
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    opacity: 0.9,
  },
});
