import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

export default function RatingStars({ rating = 0, reviewCount, size = 14 }) {
  const rounded = Math.round(rating);
  return (
    <View style={styles.row}>
      {[1, 2, 3, 4, 5].map((i) => (
        <Ionicons
          key={i}
          name={i <= rounded ? 'star' : 'star-outline'}
          size={size}
          color="#FFA800"
          style={{ marginRight: 2 }}
        />
      ))}
      <Text style={styles.text}>
        {rating.toFixed(1)}
        {reviewCount != null ? ` (${reviewCount})` : ''}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center' },
  text: {
    marginLeft: 4,
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.small,
    color: customerColors.hint,
  },
});
