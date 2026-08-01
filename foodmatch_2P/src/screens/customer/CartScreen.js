import React from 'react';
import { FlatList, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import Button from '../../components/customer/Button';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useCart } from '../../context/customer/CartContext';
import { DAYS, MEALS } from '../../utils/plan';

const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=60';
const dayLabel = (code) => DAYS.find((d) => d.code === code)?.label || code;
const mealLabel = (code) => MEALS.find((m) => m.code === code)?.label || code;

export default function CartScreen({ navigation }) {
  const { items, removeFromCart, subtotal, getPlanPrice } = useCart();

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Tu carrito de planes" />

      {items.length === 0 ? (
        <EmptyState icon="cart-outline" title="Tu carrito está vacío" message="Agrega un plan alimenticio para suscribirte." />
      ) : (
        <>
          <FlatList
            data={items}
            keyExtractor={(item) => String(item.plan.id)}
            contentContainerStyle={{ padding: 16, maxWidth: 900, width: '100%', alignSelf: 'center' }}
            renderItem={({ item }) => (
              <View style={styles.row}>
                <Image source={{ uri: item.plan.image || PLACEHOLDER_IMAGE }} style={styles.image} />
                <View style={styles.info}>
                  <Text style={styles.name} numberOfLines={1}>
                    {item.plan.title}
                  </Text>
                  <Text style={styles.restaurant}>{item.plan.branch?.name || item.plan.category_name}</Text>
                  <Text style={styles.meta}>
                    {item.plan.type === 'monthly' ? 'Mensual' : 'Semanal'} ·{' '}
                    {item.config.selectedDays.map(dayLabel).join(', ')}
                  </Text>
                  <Text style={styles.meta}>{item.config.meals.map(mealLabel).join(', ')}</Text>
                  <Text style={styles.price}>${getPlanPrice(item.plan, item.config).toFixed(0)}</Text>
                </View>
                <Pressable onPress={() => removeFromCart(item.plan.id)}>
                  <Ionicons name="trash-outline" size={20} color={customerColors.error} />
                </Pressable>
              </View>
            )}
          />

          <View style={styles.summary}>
            <View style={styles.summaryWrapper}>
              <View style={styles.summaryRow}>
                <Text style={styles.totalLabel}>Total a pagar</Text>
                <Text style={styles.totalValue}>${subtotal.toFixed(0)}</Text>
              </View>
              <Button title="Continuar" onPress={() => navigation.navigate('Checkout')} />
            </View>
          </View>
        </>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  row: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
  image: { width: 70, height: 70, borderRadius: customerRadii.default, marginRight: 12 },
  info: { flex: 1 },
  name: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text },
  restaurant: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  meta: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  price: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.primary, marginTop: 4 },
  summary: { padding: 16, borderTopWidth: 1, borderTopColor: customerColors.border },
  summaryWrapper: { maxWidth: 900, width: '100%', alignSelf: 'center' },
  summaryRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  totalLabel: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  totalValue: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.large, color: customerColors.primary },
});
