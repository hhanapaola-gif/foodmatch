import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import Header from '../../components/customer/Header';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { listMyPlanOrders } from '../../api/plans';
import { DAY_CODE_TO_WEEKDAY } from '../../utils/plan';

const WEEKDAY_LABELS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const MONTH_LABELS = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
];

// Each plan order covers one billing cycle from its start date: 7 days for
// weekly plans, 4 weeks for monthly — matches PlanController::order pricing.
function orderDateRange(order) {
  const start = new Date(order.start_date);
  const end = new Date(start);
  end.setDate(start.getDate() + (order.plan_type === 'monthly' ? 27 : 6));
  return { start, end };
}

function getDeliveriesForMonth(orders, year, month) {
  const deliveries = [];
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const now = new Date();
  const today0 = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  orders
    .filter((o) => o.status !== 'cancelled')
    .forEach((order) => {
      const { start, end } = orderDateRange(order);
      const weekdays = (order.selected_days || []).map((code) => DAY_CODE_TO_WEEKDAY[code]);

      for (let day = 1; day <= daysInMonth; day += 1) {
        const date = new Date(year, month, day);
        if (date < start || date > end) continue;
        if (!weekdays.includes(date.getDay())) continue;
        deliveries.push({
          date,
          order,
          planTitle: order.plan_title,
          category: order.category,
          status: date < today0 ? 'delivered' : 'scheduled',
        });
      }
    });

  return deliveries.sort((a, b) => a.date - b.date);
}

export default function PlanCalendarScreen() {
  const navigation = useNavigation();
  const today = new Date();
  const [cursor, setCursor] = useState({ year: today.getFullYear(), month: today.getMonth() });
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    listMyPlanOrders()
      .then((data) => setOrders(data?.orders || []))
      .catch(() => setOrders([]))
      .finally(() => setLoading(false));
  }, []);

  const deliveries = useMemo(() => getDeliveriesForMonth(orders, cursor.year, cursor.month), [orders, cursor]);
  const deliveriesByDay = useMemo(() => {
    const map = {};
    deliveries.forEach((d) => {
      map[d.date.getDate()] = map[d.date.getDate()] || [];
      map[d.date.getDate()].push(d);
    });
    return map;
  }, [deliveries]);

  const daysInMonth = new Date(cursor.year, cursor.month + 1, 0).getDate();
  const firstWeekday = new Date(cursor.year, cursor.month, 1).getDay();
  const cells = [...Array(firstWeekday).fill(null), ...Array.from({ length: daysInMonth }, (_, i) => i + 1)];

  const upcoming = deliveries.filter((d) => d.status === 'scheduled').slice(0, 8);

  const changeMonth = (delta) => {
    setCursor((prev) => {
      const date = new Date(prev.year, prev.month + delta, 1);
      return { year: date.getFullYear(), month: date.getMonth() };
    });
  };

  const isToday = (day) =>
    day === today.getDate() && cursor.month === today.getMonth() && cursor.year === today.getFullYear();

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Calendario de entregas" />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.wrapper}>
          <View style={styles.monthNav}>
            <Ionicons name="chevron-back" size={22} color={customerColors.text} onPress={() => changeMonth(-1)} />
            <Text style={styles.monthLabel}>
              {MONTH_LABELS[cursor.month]} {cursor.year}
            </Text>
            <Ionicons name="chevron-forward" size={22} color={customerColors.text} onPress={() => changeMonth(1)} />
          </View>

          <View style={styles.weekHeader}>
            {WEEKDAY_LABELS.map((label) => (
              <Text key={label} style={styles.weekHeaderText}>
                {label}
              </Text>
            ))}
          </View>

          <View style={styles.grid}>
            {cells.map((day, index) => {
              const hasDelivery = day && deliveriesByDay[day];
              return (
                <View key={index} style={styles.cell}>
                  {day && (
                    <View style={[styles.dayCircle, isToday(day) && styles.dayCircleToday]}>
                      <Text style={[styles.dayNumber, isToday(day) && styles.dayNumberToday]}>{day}</Text>
                      {hasDelivery && (
                        <View style={styles.dotsRow}>
                          {hasDelivery.slice(0, 3).map((d, i) => (
                            <View
                              key={i}
                              style={[
                                styles.dot,
                                { backgroundColor: d.status === 'delivered' ? customerColors.secondary : customerColors.status.pending },
                              ]}
                            />
                          ))}
                        </View>
                      )}
                    </View>
                  )}
                </View>
              );
            })}
          </View>

          <View style={styles.legend}>
            <View style={styles.legendItem}>
              <View style={[styles.dot, { backgroundColor: customerColors.secondary }]} />
              <Text style={styles.legendText}>Entregado</Text>
            </View>
            <View style={styles.legendItem}>
              <View style={[styles.dot, { backgroundColor: customerColors.status.pending }]} />
              <Text style={styles.legendText}>Programado</Text>
            </View>
          </View>

          <Text style={styles.sectionTitle}>Próximas entregas</Text>
          {upcoming.length === 0 ? (
            <EmptyState icon="calendar-outline" title="Sin entregas programadas" message="Suscríbete a un plan para ver aquí tu calendario." />
          ) : (
            upcoming.map((delivery, index) => (
              <Pressable
                key={index}
                style={styles.deliveryCard}
                onPress={() => navigation.navigate('OrderDetails', { order: delivery.order })}
              >
                <View style={styles.deliveryDateBox}>
                  <Text style={styles.deliveryDay}>{delivery.date.getDate()}</Text>
                  <Text style={styles.deliveryMonth}>{MONTH_LABELS[delivery.date.getMonth()].slice(0, 3)}</Text>
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.deliveryTitle}>{delivery.planTitle}</Text>
                  <Text style={styles.deliveryRestaurant}>{delivery.category}</Text>
                </View>
              </Pressable>
            ))
          )}
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  wrapper: { padding: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  monthNav: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 },
  monthLabel: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  weekHeader: { flexDirection: 'row' },
  weekHeaderText: {
    flex: 1,
    textAlign: 'center',
    fontFamily: customerFonts.medium,
    fontSize: customerFontSizes.small,
    color: customerColors.hint,
    marginBottom: 8,
  },
  grid: { flexDirection: 'row', flexWrap: 'wrap' },
  cell: { width: `${100 / 7}%`, alignItems: 'center', marginBottom: 8 },
  dayCircle: { width: 38, height: 38, borderRadius: 19, alignItems: 'center', justifyContent: 'center' },
  dayCircleToday: { backgroundColor: `${customerColors.primary}1A` },
  dayNumber: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.text },
  dayNumberToday: { fontFamily: customerFonts.bold, color: customerColors.primary },
  dotsRow: { flexDirection: 'row', position: 'absolute', bottom: 2 },
  dot: { width: 5, height: 5, borderRadius: 2.5, marginHorizontal: 1 },
  legend: { flexDirection: 'row', marginTop: 12, marginBottom: 24 },
  legendItem: { flexDirection: 'row', alignItems: 'center', marginRight: 20 },
  legendText: { marginLeft: 6, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 14 },
  deliveryCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    padding: 12,
    marginBottom: 10,
  },
  deliveryDateBox: {
    width: 48,
    height: 48,
    borderRadius: customerRadii.default,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  deliveryDay: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.large, color: customerColors.primary },
  deliveryMonth: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.extraSmall, color: customerColors.primary, textTransform: 'uppercase' },
  deliveryTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  deliveryRestaurant: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
});
