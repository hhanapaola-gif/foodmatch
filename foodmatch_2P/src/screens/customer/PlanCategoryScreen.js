import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import PlanCard from '../../components/customer/PlanCard';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { listPlans } from '../../api/plans';

export default function PlanCategoryScreen({ route }) {
  const { categoryId, categoryName } = route.params || {};
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    setLoading(true);
    listPlans({ categoryId, limit: 50 })
      .then((data) => active && setPlans(data?.plans || []))
      .catch(() => active && setPlans([]))
      .finally(() => active && setLoading(false));
    return () => {
      active = false;
    };
  }, [categoryId]);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title={categoryName || 'Planes'} />
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
          ListEmptyComponent={<EmptyState icon="nutrition-outline" title="Sin planes" message="Aún no hay planes en esta categoría." />}
          renderItem={({ item }) => (
            <PlanCard plan={item} style={[styles.gridCard, { width: `${100 / columns - 2}%` }]} />
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  list: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
  gridCard: { marginBottom: 16 },
});
