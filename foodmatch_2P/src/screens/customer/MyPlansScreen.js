import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import StatusBadge from '../../components/customer/StatusBadge';
import PlanCard from '../../components/customer/PlanCard';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { listMyPlanOrders, listPlans } from '../../api/plans';
import { DAYS, MEALS } from '../../utils/plan';

const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=200&q=60';
const dayLabel = (code) => DAYS.find((d) => d.code === code)?.label || code;

export default function MyPlansScreen() {
  const navigation = useNavigation();
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });
  const [subscriptions, setSubscriptions] = useState([]);
  const [explorePlans, setExplorePlans] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    Promise.all([listMyPlanOrders(), listPlans({ limit: 8 })])
      .then(([orders, plans]) => {
        // "Active subscriptions" should not include cancelled ones — those
        // only belong in the order history (OrderScreen).
        setSubscriptions((orders?.orders || []).filter((o) => o.status !== 'cancelled'));
        setExplorePlans(plans?.plans || []);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  useFocusEffect(useCallback(() => { load(); }, [load]));

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>Mis planes</Text>
        <Pressable style={styles.calendarButton} onPress={() => navigation.navigate('PlanCalendar')}>
          <Ionicons name="calendar-outline" size={20} color={customerColors.primary} />
        </Pressable>
      </View>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <FlatList
          data={explorePlans}
          keyExtractor={(item) => String(item.id)}
          numColumns={columns}
          key={columns}
          contentContainerStyle={styles.wrapper}
          columnWrapperStyle={columns > 1 ? styles.row : undefined}
          ListHeaderComponent={
            <View style={{ marginBottom: 8 }}>
              <Text style={styles.sectionTitle}>Suscripciones activas</Text>
              {subscriptions.length === 0 ? (
                <EmptyState icon="bag-handle-outline" title="Sin planes activos" message="Suscríbete a un plan semanal o mensual para verlo aquí." />
              ) : (
                subscriptions.map((sub) => (
                  <Pressable
                    key={sub.id}
                    style={styles.subscriptionCard}
                    onPress={() => navigation.navigate('OrderDetails', { order: sub })}
                  >
                    <Image source={{ uri: sub.plan_image || PLACEHOLDER_IMAGE }} style={styles.subscriptionImage} />
                    <View style={{ flex: 1, marginLeft: 12 }}>
                      <View style={styles.rowBetween}>
                        <Text style={styles.subscriptionTitle} numberOfLines={1}>{sub.plan_title}</Text>
                        <StatusBadge status={sub.status} />
                      </View>
                      <Text style={styles.subscriptionMeta}>{sub.category}</Text>
                      <Text style={styles.subscriptionMeta}>
                        {sub.plan_type === 'weekly' ? 'Semanal' : 'Mensual'} ·{' '}
                        {(sub.selected_days || []).map(dayLabel).join(', ')}
                      </Text>
                    </View>
                  </Pressable>
                ))
              )}
              <Text style={[styles.sectionTitle, { marginTop: 20 }]}>Explora otros planes</Text>
            </View>
          }
          renderItem={({ item }) => (
            <PlanCard plan={item} style={{ width: `${100 / columns - 2}%`, marginBottom: 16 }} />
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingTop: 8,
    paddingBottom: 4,
  },
  title: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.extraLarge, color: customerColors.text },
  calendarButton: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
  },
  wrapper: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 10 },
  subscriptionCard: {
    flexDirection: 'row',
    backgroundColor: `${customerColors.secondary}0D`,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: `${customerColors.secondary}55`,
    padding: 12,
    marginBottom: 12,
  },
  subscriptionImage: { width: 60, height: 60, borderRadius: customerRadii.default },
  rowBetween: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
  subscriptionTitle: { flex: 1, marginRight: 8, fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  subscriptionMeta: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
});
