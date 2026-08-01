import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

const LABELS = {
  pending: 'Pendiente',
  confirmed: 'Confirmado',
  processing: 'En preparación',
  outForDelivery: 'En camino',
  delivered: 'Entregado',
  cancelled: 'Cancelado',
  active: 'Activo',
};

export default function StatusBadge({ status }) {
  const color = customerColors.status[status] || customerColors.hint;
  return (
    <View style={[styles.badge, { backgroundColor: `${color}22`, borderColor: color }]}>
      <Text style={[styles.text, { color }]}>{LABELS[status] || status}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 20,
    borderWidth: 1,
  },
  text: {
    fontFamily: customerFonts.medium,
    fontSize: customerFontSizes.small,
  },
});
