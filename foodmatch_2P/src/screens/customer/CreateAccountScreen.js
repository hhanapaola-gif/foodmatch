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
  firstName: z.string().min(1, 'Ingresa tu nombre'),
  lastName: z.string().min(1, 'Ingresa tu apellido'),
  email: z.string().email('Correo inválido'),
  phone: z.string().min(6, 'Teléfono inválido'),
  password: z.string().min(6, 'La contraseña debe tener al menos 6 caracteres'),
});

export default function CreateAccountScreen({ navigation }) {
  const { register } = useAuth();
  const [serverError, setServerError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const {
    control,
    handleSubmit,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(schema),
    defaultValues: { firstName: '', lastName: '', email: '', phone: '', password: '' },
  });

  const onSubmit = async (values) => {
    setServerError('');
    setSubmitting(true);
    try {
      await register(values);
      navigation.replace('Dashboard');
    } catch (err) {
      setServerError(err.message || extractErrorMessage(err, 'No pudimos crear tu cuenta.'));
    } finally {
      setSubmitting(false);
    }
  };

  const field = (name, label, icon, extraProps = {}) => (
    <>
      <Controller
        control={control}
        name={name}
        render={({ field: { onChange, value } }) => (
          <Input label={label} icon={icon} value={value} onChangeText={onChange} style={styles.field} {...extraProps} />
        )}
      />
      {!!errors[name] && <Text style={styles.fieldError}>{errors[name].message}</Text>}
    </>
  );

  return (
    <SafeAreaView style={{ flex: 1 }} edges={['top', 'bottom']}>
      <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.container}>
        {navigation.canGoBack() && (
          <Pressable style={styles.backButton} onPress={() => navigation.goBack()}>
            <Ionicons name="chevron-back" size={24} color={customerColors.text} />
          </Pressable>
        )}
        <Text style={styles.title}>Crea tu cuenta</Text>
        <Text style={styles.subtitle}>Regístrate para suscribirte a un plan alimenticio</Text>

        {!!serverError && (
          <View style={styles.errorBox}>
            <Text style={styles.errorText}>{serverError}</Text>
          </View>
        )}

        {field('firstName', 'Nombre', 'person-outline', { placeholder: 'Tu nombre' })}
        {field('lastName', 'Apellido', 'person-outline', { placeholder: 'Tu apellido' })}
        {field('email', 'Correo electrónico', 'mail-outline', {
          placeholder: 'tucorreo@mail.com',
          keyboardType: 'email-address',
          autoCapitalize: 'none',
        })}
        {field('phone', 'Teléfono', 'call-outline', { placeholder: '+52 55 0000 0000', keyboardType: 'phone-pad' })}
        {field('password', 'Contraseña', 'lock-closed-outline', { placeholder: '••••••••', secureTextEntry: true })}

        <Button title="Crear cuenta" onPress={handleSubmit(onSubmit)} loading={submitting} style={styles.button} />

        <View style={styles.footer}>
          <Text style={styles.footerText}>¿Ya tienes cuenta? </Text>
          <Text style={styles.footerLink} onPress={() => navigation.navigate('Login')}>
            Inicia sesión
          </Text>
        </View>
      </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { padding: 24, paddingTop: 24 },
  backButton: { width: 36, height: 36, justifyContent: 'center', marginBottom: 24 },
  title: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.overLarge, color: customerColors.text },
  subtitle: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.hint, marginTop: 6, marginBottom: 24 },
  field: { marginBottom: 4 },
  fieldError: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginBottom: 12 },
  errorBox: { backgroundColor: `${customerColors.error}14`, borderRadius: 10, padding: 12, marginBottom: 16 },
  errorText: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small },
  button: { marginTop: 8 },
  footer: { flexDirection: 'row', justifyContent: 'center', marginTop: 24 },
  footerText: { fontFamily: customerFonts.regular, color: customerColors.hint },
  footerLink: { fontFamily: customerFonts.semiBold, color: customerColors.primary },
});
