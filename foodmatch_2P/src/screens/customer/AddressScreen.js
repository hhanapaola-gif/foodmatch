import React, { useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Pressable } from 'react-native';
import Header from '../../components/customer/Header';
import Button from '../../components/customer/Button';
import Input from '../../components/customer/Input';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { listAddresses, addAddress, deleteAddress } from '../../api/customer';
import { extractErrorMessage } from '../../api/client';
import { getCurrentAddress } from '../../utils/location';

const schema = z.object({
  label: z.string().min(1, 'Dale un nombre a esta dirección (ej. Casa)'),
  contactName: z.string().min(1, 'Ingresa el nombre de contacto'),
  contactPhone: z.string().min(6, 'Teléfono inválido'),
  address: z.string().min(5, 'Ingresa la dirección completa'),
});

export default function AddressScreen({ navigation }) {
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [serverError, setServerError] = useState('');
  const [locating, setLocating] = useState(false);
  const [coords, setCoords] = useState(null);

  const {
    control,
    handleSubmit,
    reset,
    setValue,
    formState: { errors },
  } = useForm({
    resolver: zodResolver(schema),
    defaultValues: { label: '', contactName: '', contactPhone: '', address: '' },
  });

  const handleUseCurrentLocation = async () => {
    setServerError('');
    setLocating(true);
    try {
      const result = await getCurrentAddress();
      setValue('address', result.address, { shouldValidate: true });
      setCoords({ latitude: result.latitude, longitude: result.longitude });
    } catch (err) {
      setServerError(err.message || 'No pudimos obtener tu ubicación.');
    } finally {
      setLocating(false);
    }
  };

  const load = () => {
    setLoading(true);
    listAddresses()
      .then(setAddresses)
      .catch(() => setAddresses([]))
      .finally(() => setLoading(false));
  };

  React.useEffect(load, []);

  const onSubmit = async (values) => {
    setServerError('');
    setSubmitting(true);
    try {
      await addAddress({ ...values, ...coords, isDefault: addresses.length === 0 });
      reset();
      setCoords(null);
      setShowForm(false);
      load();
    } catch (err) {
      setServerError(extractErrorMessage(err, 'No pudimos guardar la dirección.'));
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    try {
      await deleteAddress(id);
      load();
    } catch {
      // ignore — list stays as-is, user can retry
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Mis direcciones" />

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : showForm ? (
        <View style={styles.wrapper}>
          <Controller
            control={control}
            name="label"
            render={({ field: { onChange, value } }) => (
              <Input label="Nombre (Casa, Oficina...)" value={value} onChangeText={onChange} style={styles.field} />
            )}
          />
          {!!errors.label && <Text style={styles.fieldError}>{errors.label.message}</Text>}

          <Controller
            control={control}
            name="contactName"
            render={({ field: { onChange, value } }) => (
              <Input label="Nombre de contacto" value={value} onChangeText={onChange} style={styles.field} />
            )}
          />
          {!!errors.contactName && <Text style={styles.fieldError}>{errors.contactName.message}</Text>}

          <Controller
            control={control}
            name="contactPhone"
            render={({ field: { onChange, value } }) => (
              <Input label="Teléfono de contacto" keyboardType="phone-pad" value={value} onChangeText={onChange} style={styles.field} />
            )}
          />
          {!!errors.contactPhone && <Text style={styles.fieldError}>{errors.contactPhone.message}</Text>}

          <Controller
            control={control}
            name="address"
            render={({ field: { onChange, value } }) => (
              <Input label="Dirección completa" value={value} onChangeText={onChange} style={styles.field} />
            )}
          />
          {!!errors.address && <Text style={styles.fieldError}>{errors.address.message}</Text>}

          <Pressable style={styles.locateButton} onPress={handleUseCurrentLocation} disabled={locating}>
            {locating ? (
              <ActivityIndicator size="small" color={customerColors.primary} />
            ) : (
              <Ionicons name="locate-outline" size={18} color={customerColors.primary} />
            )}
            <Text style={styles.locateButtonText}>
              {locating ? 'Ubicando...' : 'Usar mi ubicación actual'}
            </Text>
          </Pressable>
          {!!coords && (
            <Text style={styles.locateHint}>
              <Ionicons name="checkmark-circle" size={12} color={customerColors.secondary} /> Ubicación GPS adjunta a esta dirección
            </Text>
          )}

          {!!serverError && <Text style={styles.fieldError}>{serverError}</Text>}

          <Button title="Guardar dirección" onPress={handleSubmit(onSubmit)} loading={submitting} style={{ marginTop: 8 }} />
          <Button
            title="Cancelar"
            variant="text"
            onPress={() => {
              reset();
              setCoords(null);
              setServerError('');
              setShowForm(false);
            }}
          />
        </View>
      ) : (
        <>
          <FlatList
            data={addresses}
            keyExtractor={(item) => String(item.id)}
            contentContainerStyle={styles.wrapper}
            ListEmptyComponent={<EmptyState icon="location-outline" title="Sin direcciones" message="Agrega una dirección para recibir tus pedidos." />}
            renderItem={({ item }) => (
              <View style={styles.card}>
                <View style={styles.iconWrap}>
                  <Ionicons name="location-outline" size={20} color={customerColors.primary} />
                </View>
                <View style={styles.info}>
                  <View style={styles.rowBetween}>
                    <Text style={styles.label}>{item.address_type}</Text>
                    {!!item.is_default && (
                      <View style={styles.defaultBadge}>
                        <Text style={styles.defaultText}>Predeterminada</Text>
                      </View>
                    )}
                  </View>
                  <Text style={styles.address}>{item.address}</Text>
                </View>
                <Pressable onPress={() => handleDelete(item.id)}>
                  <Ionicons name="trash-outline" size={18} color={customerColors.error} />
                </Pressable>
              </View>
            )}
          />
          <View style={styles.footer}>
            <View style={styles.footerWrapper}>
              <Button title="Agregar nueva dirección" onPress={() => setShowForm(true)} />
            </View>
          </View>
        </>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  wrapper: { padding: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  field: { marginBottom: 4 },
  fieldError: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginBottom: 12 },
  locateButton: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    marginBottom: 8,
    marginTop: -4,
  },
  locateButtonText: { marginLeft: 6, fontFamily: customerFonts.medium, fontSize: customerFontSizes.small, color: customerColors.primary },
  locateHint: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.extraSmall, color: customerColors.secondary, marginBottom: 16 },
  card: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    padding: 14,
    marginBottom: 12,
  },
  iconWrap: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  info: { flex: 1, marginRight: 8 },
  rowBetween: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  label: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  defaultBadge: { backgroundColor: `${customerColors.secondary}1A`, borderRadius: 10, paddingHorizontal: 8, paddingVertical: 2 },
  defaultText: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.extraSmall, color: customerColors.secondary },
  address: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 4 },
  footer: { padding: 16, borderTopWidth: 1, borderTopColor: customerColors.border },
  footerWrapper: { maxWidth: 700, width: '100%', alignSelf: 'center' },
});
