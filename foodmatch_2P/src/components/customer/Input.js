import React, { useState } from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';

export default function Input({ label, icon, secureTextEntry, style, ...props }) {
  const [focused, setFocused] = useState(false);
  const [hidden, setHidden] = useState(!!secureTextEntry);

  return (
    <View style={style}>
      {!!label && <Text style={styles.label}>{label}</Text>}
      <View style={[styles.wrapper, focused && styles.wrapperFocused]}>
        {!!icon && (
          <Ionicons name={icon} size={18} color={customerColors.hint} style={styles.icon} />
        )}
        <TextInput
          {...props}
          secureTextEntry={hidden}
          onFocus={() => setFocused(true)}
          onBlur={() => setFocused(false)}
          placeholderTextColor={customerColors.hint}
          style={styles.input}
        />
        {!!secureTextEntry && (
          <Ionicons
            name={hidden ? 'eye-off-outline' : 'eye-outline'}
            size={18}
            color={customerColors.hint}
            onPress={() => setHidden((v) => !v)}
          />
        )}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  label: {
    fontFamily: customerFonts.medium,
    fontSize: customerFontSizes.default,
    color: customerColors.text,
    marginBottom: 6,
  },
  wrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: customerColors.border,
    borderRadius: customerRadii.default,
    paddingHorizontal: 14,
    height: 50,
    backgroundColor: customerColors.offWhite,
  },
  wrapperFocused: {
    borderColor: customerColors.primary,
  },
  icon: {
    marginRight: 8,
  },
  input: {
    flex: 1,
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    color: customerColors.text,
  },
});
