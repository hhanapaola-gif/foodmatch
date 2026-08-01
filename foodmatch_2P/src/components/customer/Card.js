import React from 'react';
import { StyleSheet, View } from 'react-native';
import { customerColors, customerRadii } from '../../theme/customerTheme';

export default function Card({ children, style }) {
  return <View style={[styles.card, style]}>{children}</View>;
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    padding: 14,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },
});
