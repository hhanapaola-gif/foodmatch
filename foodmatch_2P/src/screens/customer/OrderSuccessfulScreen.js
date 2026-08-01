import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Button from '../../components/customer/Button';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

export default function OrderSuccessfulScreen({ navigation }) {
  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.wrapper}>
        <View style={styles.iconWrap}>
          <Ionicons name="checkmark" size={56} color="#fff" />
        </View>
        <Text style={styles.title}>¡Suscripción confirmada!</Text>
        <Text style={styles.subtitle}>
          Tu plan alimenticio quedó activo. Revisa el calendario para ver tus próximas entregas programadas.
        </Text>

        <Button
          title="Ver calendario de entregas"
          onPress={() =>
            navigation.reset({ index: 1, routes: [{ name: 'Dashboard' }, { name: 'PlanCalendar' }] })
          }
          style={styles.button}
        />
        <Button
          title="Volver al inicio"
          variant="outline"
          onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Dashboard' }] })}
          style={{ width: '100%' }}
        />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background, alignItems: 'center', justifyContent: 'center', padding: 32 },
  wrapper: { width: '100%', maxWidth: 420, alignItems: 'center' },
  iconWrap: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: customerColors.secondary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
  },
  title: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.overLarge, color: customerColors.text, marginBottom: 10, textAlign: 'center' },
  subtitle: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.hint, textAlign: 'center', marginBottom: 32 },
  button: { marginBottom: 12, width: '100%' },
});
