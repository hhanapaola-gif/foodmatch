import React, { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import Input from '../../components/customer/Input';
import Button from '../../components/customer/Button';
import { useAuth } from '../../context/customer/AuthContext';
import { extractErrorMessage } from '../../api/client';

const schema = z.object({
  emailOrPhone: z.string().min(1, 'Ingresa tu correo o teléfono'),
  password: z.string().min(6, 'La contraseña debe tener al menos 6 caracteres'),
});

export default function LoginScreen({ navigation }) {
  const { login } = useAuth();
  const [serverError, setServerError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const {
    control,
    handleSubmit,
    formState: { errors },
  } = useForm({ resolver: zodResolver(schema), defaultValues: { emailOrPhone: '', password: '' } });

  const onSubmit = async ({ emailOrPhone, password }) => {
    setServerError('');
    setSubmitting(true);
    try {
      await login(emailOrPhone, password);
      navigation.replace('Dashboard');
    } catch (err) {
      setServerError(err.message || extractErrorMessage(err, 'No pudimos iniciar sesión. Revisa tus datos.'));
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={{ flex: 1 }} edges={['top', 'bottom']}>
      <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.container}>
        {navigation.canGoBack() && (
          <Pressable style={styles.backButton} onPress={() => navigation.goBack()}>
            <Ionicons name="chevron-back" size={24} color={customerColors.text} />
          </Pressable>
        )}
        <Text style={styles.title}>Inicia sesión</Text>
        <Text style={styles.subtitle}>Ingresa tus datos para continuar</Text>

        {!!serverError && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{serverError}</Text>
          </View>
        )}

        <Controller
          control={control}
          name="emailOrPhone"
          render={({ field: { onChange, value } }) => (
            <Input
              label="Correo o teléfono"
              icon="mail-outline"
              placeholder="tucorreo@mail.com"
              keyboardType="email-address"
              autoCapitalize="none"
              value={value}
              onChangeText={onChange}
              style={styles.field}
            />
          )}
        />
        {!!errors.emailOrPhone && <Text style={styles.fieldError}>{errors.emailOrPhone.message}</Text>}

        <Controller
          control={control}
          name="password"
          render={({ field: { onChange, value } }) => (
            <Input
              label="Contraseña"
              icon="lock-closed-outline"
              placeholder="••••••••"
              secureTextEntry
              value={value}
              onChangeText={onChange}
              style={styles.field}
            />
          )}
        />
        {!!errors.password && <Text style={styles.fieldError}>{errors.password.message}</Text>}

        <Button title="Iniciar sesión" onPress={handleSubmit(onSubmit)} loading={submitting} style={styles.button} />

        <View style={styles.footer}>
          <Text style={styles.footerText}>¿No tienes cuenta? </Text>
          <Text style={styles.footerLink} onPress={() => navigation.navigate('CreateAccount')}>
            Crear cuenta
          </Text>
        </View>
      </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { padding: 24, paddingTop: 24 },
  backButton: { width: 36, height: 36, justifyContent: 'center', marginBottom: 32 },
  title: {
    fontFamily: customerFonts.bold,
    fontSize: customerFontSizes.overLarge,
    color: customerColors.text,
  },
  subtitle: {
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    color: customerColors.hint,
    marginTop: 6,
    marginBottom: 28,
  },
  field: { marginBottom: 4 },
  fieldError: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginBottom: 12 },
  errorBox: { backgroundColor: `${customerColors.error}14`, borderRadius: 10, padding: 12, marginBottom: 16 },
  errorText: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small },
  button: { marginTop: 8 },
  footer: { flexDirection: 'row', justifyContent: 'center', marginTop: 24 },
  footerText: { fontFamily: customerFonts.regular, color: customerColors.hint },
  footerLink: { fontFamily: customerFonts.semiBold, color: customerColors.primary },
});
