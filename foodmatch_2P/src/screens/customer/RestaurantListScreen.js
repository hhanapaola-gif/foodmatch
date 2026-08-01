import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { listBranches } from '../../api/plans';

const RESTAURANT_PLACEHOLDER = 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=500&q=60';

export default function RestaurantListScreen({ navigation }) {
  const columns = useColumnCount({ base: 1, tablet: 2, desktop: 3, wide: 3 });
  const [branches, setBranches] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    listBranches()
      .then(setBranches)
      .catch(() => setBranches([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Restaurantes socios" />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <FlatList
          data={branches}
          keyExtractor={(item) => String(item.id)}
          numColumns={columns}
          key={columns}
          contentContainerStyle={styles.list}
          columnWrapperStyle={columns > 1 ? styles.row : undefined}
          ListEmptyComponent={<EmptyState icon="storefront-outline" title="Sin restaurantes" message="Todavía no hay restaurantes socios." />}
          renderItem={({ item }) => (
            <Pressable
              style={[styles.card, { width: `${100 / columns - 2}%` }]}
              onPress={() => navigation.navigate('RestaurantPlans', { restaurant: item })}
            >
              <Image source={{ uri: RESTAURANT_PLACEHOLDER }} style={styles.image} />
              <View style={styles.info}>
                <Text style={styles.name}>{item.name}</Text>
                <Text style={styles.meta} numberOfLines={1}>
                  {item.address || 'Restaurante socio'}
                </Text>
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
  list: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
  card: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    marginBottom: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },
  image: { width: '100%', height: 150 },
  info: { padding: 14 },
  name: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  meta: { marginTop: 6, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
});
