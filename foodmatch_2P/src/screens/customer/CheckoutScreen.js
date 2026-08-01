import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import Button from '../../components/customer/Button';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useCart } from '../../context/customer/CartContext';
import { listAddresses } from '../../api/customer';
import { MEALS, DAYS } from '../../utils/plan';

const dayLabel = (code) => DAYS.find((d) => d.code === code)?.label || code;
const mealLabel = (code) => MEALS.find((m) => m.code === code)?.label || code;

const PAYMENT_METHODS = [
  { id: 'cash', label: 'Efectivo', icon: 'cash-outline' },
  { id: 'card', label: 'Tarjeta', icon: 'card-outline' },
];

export default function CheckoutScreen({ navigation }) {
  const { items, subtotal, getPlanPrice } = useCart();
  const [addresses, setAddresses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedAddressId, setSelectedAddressId] = useState(null);
  const [selectedPayment, setSelectedPayment] = useState('cash');

  const load = () => {
    setLoading(true);
    listAddresses()
      .then((data) => {
        setAddresses(data || []);
        setSelectedAddressId((data || []).find((a) => a.is_default)?.id ?? data?.[0]?.id ?? null);
      })
      .catch(() => setAddresses([]))
      .finally(() => setLoading(false));
  };

  useEffect(load, []);
  // reload when coming back from the "Add address" screen
  useEffect(() => navigation.addListener('focus', load), [navigation]);

  const selectedAddress = addresses.find((a) => a.id === selectedAddressId);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Confirmar suscripción" />
      <ScrollView contentContainerStyle={styles.wrapper}>
        <Text style={styles.sectionTitle}>Dirección de entrega</Text>
        {loading ? (
          <ActivityIndicator color={customerColors.primary} style={{ marginBottom: 16 }} />
        ) : addresses.length === 0 ? (
          <EmptyState icon="location-outline" title="Sin direcciones" message="Agrega una dirección para continuar." />
        ) : (
          addresses.map((addr) => (
            <Pressable
              key={addr.id}
              style={[styles.optionRow, selectedAddressId === addr.id && styles.optionRowActive]}
              onPress={() => setSelectedAddressId(addr.id)}
            >
              <Ionicons name="location-outline" size={20} color={customerColors.primary} />
              <View style={styles.optionText}>
                <Text style={styles.optionTitle}>{addr.address_type}</Text>
                <Text style={styles.optionSubtitle}>{addr.address}</Text>
              </View>
              <Ionicons
                name={selectedAddressId === addr.id ? 'radio-button-on' : 'radio-button-off'}
                size={20}
                color={customerColors.primary}
              />
            </Pressable>
          ))
        )}
        <Button title="Agregar nueva dirección" variant="outline" onPress={() => navigation.navigate('Address')} style={{ marginBottom: 8 }} />

        <Text style={styles.sectionTitle}>Método de pago</Text>
        {PAYMENT_METHODS.map((method) => (
          <Pressable
            key={method.id}
            style={[styles.optionRow, selectedPayment === method.id && styles.optionRowActive]}
            onPress={() => setSelectedPayment(method.id)}
          >
            <Ionicons name={method.icon} size={20} color={customerColors.primary} />
            <Text style={[styles.optionTitle, { flex: 1, marginLeft: 12 }]}>{method.label}</Text>
            <Ionicons
              name={selectedPayment === method.id ? 'radio-button-on' : 'radio-button-off'}
              size={20}
              color={customerColors.primary}
            />
          </Pressable>
        ))}

        <Text style={styles.sectionTitle}>Resumen de tu suscripción</Text>
        {items.map((item) => (
          <View key={item.plan.id} style={styles.summaryItem}>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel} numberOfLines={1}>
                {item.plan.title} ({item.plan.type === 'monthly' ? 'mensual' : 'semanal'})
              </Text>
              <Text style={styles.summaryValue}>${getPlanPrice(item.plan, item.config).toFixed(0)}</Text>
            </View>
            <Text style={styles.summaryDetail}>
              {item.config.selectedDays.map(dayLabel).join(', ')} · {item.config.meals.map(mealLabel).join(', ')}
            </Text>
          </View>
        ))}
        <View style={styles.summaryRow}>
          <Text style={styles.totalLabel}>Total</Text>
          <Text style={styles.totalValue}>${subtotal.toFixed(0)}</Text>
        </View>
      </ScrollView>
      <View style={styles.footer}>
        <View style={styles.footerWrapper}>
          <Button
            title="Continuar al pago"
            disabled={!selectedAddress}
            onPress={() =>
              navigation.navigate('Payment', {
                addressText: selectedAddress?.address,
                paymentMethod: selectedPayment,
              })
            }
          />
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { padding: 16, maxWidth: 900, width: '100%', alignSelf: 'center' },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 12, marginTop: 8 },
  optionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: customerColors.border,
    borderRadius: customerRadii.default,
    padding: 14,
    marginBottom: 10,
  },
  optionRowActive: { borderColor: customerColors.primary, backgroundColor: `${customerColors.primary}0D` },
  optionText: { flex: 1, marginLeft: 12 },
  optionTitle: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text },
  optionSubtitle: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  summaryItem: { marginBottom: 8 },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between' },
  summaryLabel: { flex: 1, marginRight: 8, fontFamily: customerFonts.regular, color: customerColors.hint },
  summaryValue: { fontFamily: customerFonts.medium, color: customerColors.text },
  summaryDetail: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  totalLabel: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  totalValue: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.large, color: customerColors.primary },
  footer: { padding: 16, borderTopWidth: 1, borderTopColor: customerColors.border },
  footerWrapper: { maxWidth: 900, width: '100%', alignSelf: 'center' },
});
