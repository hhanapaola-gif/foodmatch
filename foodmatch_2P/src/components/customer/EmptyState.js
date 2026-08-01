import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

export default function EmptyState({ icon = 'file-tray-outline', title, message }) {
  return (
    <View style={styles.container}>
      <Ionicons name={icon} size={64} color={customerColors.disabled} />
      {!!title && <Text style={styles.title}>{title}</Text>}
      {!!message && <Text style={styles.message}>{message}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { alignItems: 'center', justifyContent: 'center', padding: 32 },
  title: {
    marginTop: 16,
    fontFamily: customerFonts.semiBold,
    fontSize: customerFontSizes.large,
    color: customerColors.text,
  },
  message: {
    marginTop: 6,
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    color: customerColors.hint,
    textAlign: 'center',
  },
});
