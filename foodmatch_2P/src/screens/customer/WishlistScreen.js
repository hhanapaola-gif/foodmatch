import React from 'react';
import { FlatList, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import PlanCard from '../../components/customer/PlanCard';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { useWishlist } from '../../context/customer/WishlistContext';

export default function WishlistScreen() {
  const { wishlistPlans } = useWishlist();
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>Guardados</Text>
      </View>
      <FlatList
        data={wishlistPlans}
        keyExtractor={(item) => item.id}
        numColumns={columns}
        key={columns}
        contentContainerStyle={styles.wrapper}
        columnWrapperStyle={columns > 1 ? styles.row : undefined}
        ListEmptyComponent={
          <EmptyState icon="heart-outline" title="Nada guardado todavía" message="Toca el corazón en un plan para guardarlo aquí." />
        }
        renderItem={({ item }) => (
          <PlanCard plan={item} style={{ width: `${100 / columns - 2}%`, marginBottom: 16 }} />
        )}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  header: { paddingHorizontal: 16, paddingTop: 8, paddingBottom: 4 },
  title: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.extraLarge, color: customerColors.text },
  wrapper: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
});
