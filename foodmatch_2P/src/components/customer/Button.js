import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text } from 'react-native';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';

export default function Button({ title, onPress, variant = 'primary', disabled, loading, style }) {
  const isOutline = variant === 'outline';
  const isText = variant === 'text';

  return (
    <Pressable
      onPress={onPress}
      disabled={disabled || loading}
      style={({ pressed }) => [
        styles.base,
        isOutline && styles.outline,
        isText && styles.text,
        !isOutline && !isText && { backgroundColor: customerColors.primary },
        (disabled || loading) && { opacity: 0.5 },
        pressed && { opacity: 0.85 },
        style,
      ]}
    >
      {loading ? (
        <ActivityIndicator color={isOutline || isText ? customerColors.primary : '#fff'} />
      ) : (
        <Text
          style={[
            styles.label,
            isOutline && { color: customerColors.primary },
            isText && { color: customerColors.primary },
            !isOutline && !isText && { color: '#fff' },
          ]}
        >
          {title}
        </Text>
      )}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  base: {
    height: 50,
    borderRadius: customerRadii.default,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  outline: {
    backgroundColor: 'transparent',
    borderWidth: 1.5,
    borderColor: customerColors.primary,
  },
  text: {
    backgroundColor: 'transparent',
    height: 40,
  },
  label: {
    fontFamily: customerFonts.semiBold,
    fontSize: customerFontSizes.large,
  },
});
