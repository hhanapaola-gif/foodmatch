import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

export default function PriceTag({ price, discount, discountType = 'percent', size = 'default' }) {
  const hasDiscount = discount > 0;
  const finalPrice = hasDiscount && discountType === 'percent' ? price - (price * discount) / 100 : price;
  const fontSize = size === 'large' ? customerFontSizes.extraLarge : customerFontSizes.large;

  return (
    <View style={styles.row}>
      <Text style={[styles.price, { fontSize }]}>${finalPrice.toFixed(2)}</Text>
      {hasDiscount && <Text style={styles.original}>${price.toFixed(2)}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'baseline' },
  price: {
    fontFamily: customerFonts.bold,
    color: customerColors.primary,
  },
  original: {
    marginLeft: 6,
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.small,
    color: customerColors.hint,
    textDecorationLine: 'line-through',
  },
});
