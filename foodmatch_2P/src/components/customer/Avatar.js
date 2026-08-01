import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { customerColors, customerFonts } from '../../theme/customerTheme';

function initialFor(name) {
  const trimmed = (name || '').trim();
  return trimmed ? trimmed[0].toUpperCase() : '?';
}

// Customers have no photo upload feature — this always renders the first
// initial in a colored circle instead of loading a remote image, which also
// avoids avatars silently failing to load on devices with restricted network
// access to external image hosts.
export default function Avatar({ name, size = 40, style }) {
  const dimension = { width: size, height: size, borderRadius: size / 2 };
  return (
    <View style={[styles.circle, dimension, style]}>
      <Text style={[styles.letter, { fontSize: size * 0.42 }]}>{initialFor(name)}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  circle: {
    backgroundColor: customerColors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  letter: {
    color: customerColors.white,
    fontFamily: customerFonts.semiBold,
  },
});
