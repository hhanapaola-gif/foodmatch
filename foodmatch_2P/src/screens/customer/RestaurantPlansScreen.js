import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, Image, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import PlanCard from '../../components/customer/PlanCard';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { listPlansByRestaurant } from '../../api/plans';
import { branchImageUri } from '../../utils/branch';

export default function RestaurantPlansScreen({ route }) {
  const { restaurant } = route.params || {};
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!restaurant) return;
    listPlansByRestaurant(restaurant.id)
      .then((data) => setPlans(data?.plans || []))
      .catch(() => setPlans([]))
      .finally(() => setLoading(false));
  }, [restaurant]);

  if (!restaurant) return null;

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title={restaurant.name} />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <FlatList
          data={plans}
          keyExtractor={(item) => String(item.id)}
          numColumns={columns}
          key={columns}
          contentContainerStyle={styles.list}
          columnWrapperStyle={columns > 1 ? styles.row : undefined}
          ListEmptyComponent={<EmptyState icon="nutrition-outline" title="Sin planes disponibles" />}
          ListHeaderComponent={
            <View style={styles.restaurantHeader}>
              <Image source={{ uri: branchImageUri(restaurant) }} style={styles.image} />
              <Text style={styles.meta}>{restaurant.address || 'Restaurante socio'}</Text>
              <Text style={styles.sectionTitle}>Planes disponibles</Text>
            </View>
          }
          renderItem={({ item }) => (
            <PlanCard plan={item} style={[styles.gridCard, { width: `${100 / columns - 2}%` }]} />
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  list: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
  gridCard: { marginBottom: 16 },
  restaurantHeader: { marginBottom: 8 },
  image: { width: '100%', height: 160, borderRadius: 16, marginBottom: 10 },
  meta: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginBottom: 18 },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 14 },
});
