import React, { useEffect, useState } from 'react';
import { ActivityIndicator, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import { listPlanCategories, listBranches } from '../../api/plans';
import { categoryIcon, hasRealCategoryImage } from '../../utils/category';
import { branchImageUri } from '../../utils/branch';

export default function MenuScreen() {
  const navigation = useNavigation();
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 5 });
  const [categories, setCategories] = useState([]);
  const [branches, setBranches] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([listPlanCategories(), listBranches()])
      .then(([cats, branchList]) => {
        setCategories(cats || []);
        setBranches(branchList || []);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>Menú de planes</Text>
        <Pressable style={styles.searchButton} onPress={() => navigation.navigate('Search')}>
          <Ionicons name="search-outline" size={20} color={customerColors.text} />
        </Pressable>
      </View>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <ScrollView contentContainerStyle={styles.wrapper}>
          <Text style={styles.sectionTitle}>Explora por tipo de dieta</Text>
          <View style={styles.grid}>
            {categories.map((item) => (
              <Pressable
                key={item.id}
                style={[styles.card, { width: `${100 / columns - 2}%` }]}
                onPress={() => navigation.navigate('PlanCategory', { categoryId: item.id, categoryName: item.name })}
              >
                <View style={styles.iconWrap}>
                  {hasRealCategoryImage(item) ? (
                    <Image source={{ uri: item.image }} style={styles.iconImage} />
                  ) : (
                    <Ionicons name={categoryIcon(item.name)} size={30} color={customerColors.primary} />
                  )}
                </View>
                <Text style={styles.cardTitle}>{item.name}</Text>
              </Pressable>
            ))}
          </View>

          <Text style={[styles.sectionTitle, { marginTop: 24 }]}>Restaurantes socios</Text>
          <View style={styles.restaurantList}>
            {branches.map((restaurant) => (
              <Pressable
                key={restaurant.id}
                style={styles.restaurantRow}
                onPress={() => navigation.navigate('RestaurantPlans', { restaurant })}
              >
                <View style={styles.restaurantLogoWrap}>
                  <Image source={{ uri: branchImageUri(restaurant) }} style={styles.restaurantLogoImage} />
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.restaurantName}>{restaurant.name}</Text>
                  <Text style={styles.restaurantMeta} numberOfLines={1}>{restaurant.address || 'Restaurante socio'}</Text>
                </View>
                <Ionicons name="chevron-forward" size={18} color={customerColors.hint} />
              </Pressable>
            ))}
          </View>
        </ScrollView>
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
  },
  title: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.extraLarge, color: customerColors.text },
  searchButton: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: customerColors.lightGrayBackground,
    alignItems: 'center',
    justifyContent: 'center',
  },
  wrapper: { width: '100%', maxWidth: 1180, alignSelf: 'center', padding: 16 },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 14 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  card: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    alignItems: 'center',
    paddingVertical: 24,
    marginBottom: 14,
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },
  iconWrap: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
    overflow: 'hidden',
  },
  iconImage: {
    width: '100%',
    height: '100%',
  },
  cardTitle: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text, textAlign: 'center' },
  restaurantList: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    overflow: 'hidden',
  },
  restaurantRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 14,
    borderBottomWidth: 1,
    borderBottomColor: customerColors.border,
  },
  restaurantLogoWrap: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
    overflow: 'hidden',
  },
  restaurantLogoImage: {
    width: '100%',
    height: '100%',
  },
  restaurantName: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  restaurantMeta: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
});
