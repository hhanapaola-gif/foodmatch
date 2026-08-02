import React from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import Button from '../../components/customer/Button';
import { useAuth } from '../../context/customer/AuthContext';

export default function WelcomeScreen({ navigation }) {
  const { continueAsGuest } = useAuth();

  return (
    // top edge deliberately excluded: the hero image bleeds under the status bar/notch
    <SafeAreaView style={styles.container} edges={['bottom']}>
      <Image source={require('../../../assets/bienvenida.png')} style={styles.image} />
      <View style={styles.content}>
        <Text style={styles.title}>Bienvenido a FoodMatch</Text>
        <Text style={styles.subtitle}>Inicia sesión para suscribirte a un plan alimenticio y gestionar tus entregas.</Text>
        <Button title="Iniciar sesión" onPress={() => navigation.navigate('Login')} style={styles.button} />
        <Button
          title="Crear cuenta"
          variant="outline"
          onPress={() => navigation.navigate('CreateAccount')}
          style={styles.button}
        />
        <Button
          title="Continuar como invitado"
          variant="text"
          onPress={() => {
            continueAsGuest();
            navigation.replace('Dashboard');
          }}
        />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  image: { width: '100%', height: '45%' },
  content: { flex: 1, padding: 24, justifyContent: 'center' },
  title: {
    fontFamily: customerFonts.bold,
    fontSize: customerFontSizes.overLarge,
    color: customerColors.text,
    marginBottom: 10,
  },
  subtitle: {
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    color: customerColors.hint,
    marginBottom: 28,
  },
  button: { marginBottom: 12 },
});
