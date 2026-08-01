import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

export default function Header({ title, showBack = true, right }) {
  const navigation = useNavigation();

  return (
    <View style={styles.container}>
      <View style={styles.side}>
        {showBack && navigation.canGoBack() && (
          <Ionicons
            name="chevron-back"
            size={24}
            color={customerColors.text}
            onPress={() => navigation.goBack()}
          />
        )}
      </View>
      <Text style={styles.title} numberOfLines={1}>
        {title}
      </Text>
      <View style={[styles.side, styles.rightSide]}>{right}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: customerColors.background,
    borderBottomWidth: 1,
    borderBottomColor: customerColors.border,
  },
  side: { width: 36 },
  rightSide: { alignItems: 'flex-end' },
  title: {
    flex: 1,
    textAlign: 'center',
    fontFamily: customerFonts.semiBold,
    fontSize: customerFontSizes.extraLarge,
    color: customerColors.text,
  },
});
