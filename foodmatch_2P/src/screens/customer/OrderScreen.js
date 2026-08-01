import React, { useCallback, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from '@react-navigation/native';
import Header from '../../components/customer/Header';
import StatusBadge from '../../components/customer/StatusBadge';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { listMyPlanOrders } from '../../api/plans';

export default function OrderScreen({ navigation }) {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useFocusEffect(
    useCallback(() => {
      listMyPlanOrders()
        .then((data) => setOrders(data?.orders || []))
        .catch(() => setOrders([]))
        .finally(() => setLoading(false));
    }, [])
  );

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header
        title="Mis pedidos"
        right={
          <Ionicons name="calendar-outline" size={22} color={customerColors.text} onPress={() => navigation.navigate('PlanCalendar')} />
        }
      />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={{ padding: 16, maxWidth: 900, width: '100%', alignSelf: 'center' }}
          ListEmptyComponent={<EmptyState icon="receipt-outline" title="Sin pedidos" message="Todavía no tienes suscripciones a planes." />}
          renderItem={({ item }) => (
            <Pressable style={styles.card} onPress={() => navigation.navigate('OrderDetails', { order: item })}>
              <View style={styles.rowBetween}>
                <Text style={styles.orderId}>Pedido #{item.id}</Text>
                <StatusBadge status={item.status} />
              </View>
              <Text style={styles.planTitle}>{item.plan_title}</Text>
              <Text style={styles.branch}>{item.category}</Text>
              <View style={styles.rowBetween}>
                <Text style={styles.date}>{item.created_at}</Text>
                <Text style={styles.total}>${Number(item.total_price).toFixed(2)}</Text>
              </View>
            </Pressable>
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  card: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    padding: 14,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: customerColors.border,
  },
  rowBetween: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  orderId: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  planTitle: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text, marginBottom: 2 },
  branch: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginBottom: 6 },
  date: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
  total: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.default, color: customerColors.primary },
});
