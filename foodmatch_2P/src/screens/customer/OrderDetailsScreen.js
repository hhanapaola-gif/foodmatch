import React, { useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import StatusBadge from '../../components/customer/StatusBadge';
import Button from '../../components/customer/Button';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import { DAYS, MEALS } from '../../utils/plan';
import { cancelPlanOrder } from '../../api/plans';
import { extractErrorMessage } from '../../api/client';

const dayLabel = (code) => DAYS.find((d) => d.code === code)?.label || code;
const mealLabel = (code) => MEALS.find((m) => m.code === code)?.label || code;

export default function OrderDetailsScreen({ route, navigation }) {
  const { order: initialOrder } = route.params || {};
  const [order, setOrder] = useState(initialOrder);
  const [cancelling, setCancelling] = useState(false);

  if (!order) return null;

  const canCancel = order.status === 'confirmed' && new Date(order.start_date) > new Date();

  const handleCancel = () => {
    Alert.alert('Cancelar pedido', '¿Seguro que quieres cancelar este pedido?', [
      { text: 'No', style: 'cancel' },
      {
        text: 'Sí, cancelar',
        style: 'destructive',
        onPress: async () => {
          setCancelling(true);
          try {
            await cancelPlanOrder(order.id);
            setOrder({ ...order, status: 'cancelled' });
          } catch (err) {
            Alert.alert('No se pudo cancelar', extractErrorMessage(err));
          } finally {
            setCancelling(false);
          }
        },
      },
    ]);
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title={`Pedido #${order.id}`} />
      <ScrollView contentContainerStyle={styles.wrapper}>
        <View style={styles.headerRow}>
          <StatusBadge status={order.status} />
          <Text style={styles.date}>{order.created_at}</Text>
        </View>

        <Text style={styles.sectionTitle}>Plan</Text>
        <Text style={styles.branch}>
          {order.plan_title} · {order.category}
        </Text>

        <Text style={styles.sectionTitle}>Frecuencia y entregas</Text>
        <Text style={styles.branch}>{order.plan_type === 'weekly' ? 'Semanal' : 'Mensual'}</Text>
        <Text style={styles.branch}>Días: {(order.selected_days || []).map(dayLabel).join(', ')}</Text>
        <Text style={styles.branch}>Comidas: {(order.meals || []).map(mealLabel).join(', ')}</Text>
        <Text style={styles.branch}>Inicia: {order.start_date}</Text>

        <Text style={styles.sectionTitle}>Dirección de entrega</Text>
        <Text style={styles.branch}>{order.address || 'No especificada'}</Text>

        {!!order.notes && (
          <>
            <Text style={styles.sectionTitle}>Notas</Text>
            <Text style={styles.branch}>{order.notes}</Text>
          </>
        )}

        <View style={styles.divider} />
        <View style={styles.itemRow}>
          <Text style={styles.totalLabel}>Total</Text>
          <Text style={styles.totalValue}>${Number(order.total_price).toFixed(2)}</Text>
        </View>
        <Text style={styles.paymentInfo}>
          Pago: {order.payment_status === 'paid' ? 'Pagado' : 'Pendiente de pago'}
        </Text>

        {canCancel && (
          <Button
            title="Cancelar pedido"
            variant="outline"
            onPress={handleCancel}
            loading={cancelling}
            style={styles.cancelButton}
          />
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { padding: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
  date: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text, marginTop: 12, marginBottom: 4 },
  branch: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.hint },
  itemRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 6 },
  divider: { height: 1, backgroundColor: customerColors.border, marginVertical: 10 },
  totalLabel: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  totalValue: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.large, color: customerColors.primary },
  paymentInfo: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 12 },
  cancelButton: { marginTop: 20, borderColor: customerColors.error },
});
