import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, Image, Pressable, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useColumnCount, useIsWideScreen } from '../../utils/responsive';
import PlanCard from '../../components/customer/PlanCard';
import BannerCarousel from '../../components/customer/BannerCarousel';
import Avatar from '../../components/customer/Avatar';
import { useAuth } from '../../context/customer/AuthContext';
import { useCart } from '../../context/customer/CartContext';
import { listPlanCategories, listPlans, listBranches, getPlan } from '../../api/plans';
import { listAddresses } from '../../api/customer';
import { listBanners } from '../../api/banners';
import { branchImageUri } from '../../utils/branch';

function categoryIcon(name = '') {
  const n = name.toLowerCase();
  if (n.includes('vegan')) return 'flower-outline';
  if (n.includes('veget')) return 'leaf-outline';
  if (n.includes('prote')) return 'barbell-outline';
  if (n.includes('gluten')) return 'checkmark-circle-outline';
  if (n.includes('famil')) return 'people-outline';
  if (n.includes('calor') || n.includes('carbo')) return 'flame-outline';
  if (n.includes('fit')) return 'fitness-outline';
  return 'nutrition-outline';
}

export default function HomeScreen({ navigation }) {
  const { user, isGuest, isAuthenticated } = useAuth();
  const { itemCount } = useCart();
  const isWide = useIsWideScreen();
  const planColumns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });
  const restaurantColumns = useColumnCount({ base: 1, tablet: 2, desktop: 3, wide: 3 });

  const [categories, setCategories] = useState([]);
  const [plans, setPlans] = useState([]);
  const [branches, setBranches] = useState([]);
  const [banners, setBanners] = useState([]);
  const [deliveryAddress, setDeliveryAddress] = useState(null);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setError('');
    try {
      const [cats, planData, branchData, bannerData] = await Promise.all([
        listPlanCategories(),
        listPlans({ limit: 8 }),
        listBranches(),
        listBanners(),
      ]);
      setCategories(cats || []);
      setPlans(planData?.plans || []);
      setBranches(branchData || []);
      setBanners(bannerData || []);
    } catch {
      setError('No pudimos cargar los planes. Desliza para reintentar.');
    }

    if (isAuthenticated) {
      try {
        const addresses = await listAddresses();
        setDeliveryAddress(addresses?.find((a) => a.is_default) || addresses?.[0] || null);
      } catch {
        setDeliveryAddress(null);
      }
    } else {
      setDeliveryAddress(null);
    }
  }, [isAuthenticated]);

  useEffect(() => {
    setLoading(true);
    load().finally(() => setLoading(false));
  }, [load]);

  // Reload the address in case the user just added/changed one on the Address screen.
  useEffect(() => navigation.addListener('focus', load), [navigation, load]);

  const onRefresh = async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  };

  const handleBannerPress = async (banner) => {
    if (banner.plan) {
      // The API embeds the full plan directly on the banner, so this is an
      // instant local navigation with no network round-trip.
      navigation.navigate('PlanDetail', { plan: banner.plan });
    } else if (banner.plan_id) {
      // Fallback for older cached responses that only carry the id.
      try {
        const plan = await getPlan(banner.plan_id);
        navigation.navigate('PlanDetail', { plan });
      } catch {
        // Ignore: banner points at a plan that no longer exists/loads.
      }
    } else if (banner.category_id) {
      navigation.navigate('PlanCategory', { categoryId: banner.category_id });
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      <View style={styles.header}>
        <View style={styles.wrapper}>
          <View style={styles.headerRow}>
            <View style={{ flex: 1, marginRight: 12 }}>
              <Text style={styles.greeting}>Hola{user ? `, ${user.name.split(' ')[0]}` : isGuest ? ', invitado' : ''} </Text>
              <Pressable
                style={styles.locationRow}
                onPress={() => navigation.navigate(isAuthenticated ? 'Address' : 'Login')}
              >
                <Ionicons name="location-outline" size={14} color={customerColors.hint} />
                <Text style={styles.location} numberOfLines={1}>
                  {deliveryAddress
                    ? deliveryAddress.address
                    : isAuthenticated
                    ? 'Agrega tu dirección de entrega'
                    : 'Entregando en tu zona'}
                </Text>
              </Pressable>
            </View>
            <View style={styles.headerIcons}>
              <Pressable onPress={() => navigation.navigate('Profile')} style={styles.iconButton}>
                {user ? (
                  <Avatar name={user.name} size={26} />
                ) : (
                  <Ionicons name="person-circle-outline" size={24} color={customerColors.text} />
                )}
              </Pressable>
              <Pressable onPress={() => navigation.navigate('Notification')} style={styles.iconButton}>
                <Ionicons name="notifications-outline" size={22} color={customerColors.text} />
              </Pressable>
              <Pressable onPress={() => navigation.navigate('Cart')} style={styles.iconButton}>
                <Ionicons name="cart-outline" size={22} color={customerColors.text} />
                {itemCount > 0 && (
                  <View style={styles.badge}>
                    <Text style={styles.badgeText}>{itemCount}</Text>
                  </View>
                )}
              </Pressable>
            </View>
          </View>
        </View>
      </View>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <ScrollView
          showsVerticalScrollIndicator={false}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        >
          <View style={styles.wrapper}>
            <Pressable style={styles.searchBar} onPress={() => navigation.navigate('Search')}>
              <Ionicons name="search-outline" size={18} color={customerColors.hint} />
              <Text style={styles.searchPlaceholder}>Busca un plan alimenticio o restaurante</Text>
            </Pressable>

            {!!error && <Text style={styles.errorText}>{error}</Text>}

            <BannerCarousel banners={banners} onPressBanner={handleBannerPress} />

            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Elige tu tipo de plan</Text>
            </View>
            <View style={styles.categoryGrid}>
              {categories.map((item) => (
                <Pressable
                  key={item.id}
                  style={styles.categoryItem}
                  onPress={() => navigation.navigate('PlanCategory', { categoryId: item.id, categoryName: item.name })}
                >
                  <View style={styles.categoryIconWrap}>
                    <Ionicons name={categoryIcon(item.name)} size={24} color={customerColors.primary} />
                  </View>
                  <Text style={styles.categoryLabel} numberOfLines={2}>
                    {item.name}
                  </Text>
                </Pressable>
              ))}
            </View>

            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Planes populares</Text>
            </View>
            <View style={styles.grid}>
              {plans.map((item) => (
                <PlanCard key={item.id} plan={item} style={{ width: `${100 / planColumns - 2}%`, marginBottom: 16 }} />
              ))}
            </View>

            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Restaurantes destacados</Text>
              <Text style={styles.sectionLink} onPress={() => navigation.navigate('RestaurantList')}>
                Ver todos
              </Text>
            </View>
            <View style={[styles.grid, { paddingBottom: 24 }]}>
              {branches.slice(0, restaurantColumns * 2).map((restaurant) => (
                <Pressable
                  key={restaurant.id}
                  style={[styles.restaurantCard, { width: `${100 / restaurantColumns - 2}%` }]}
                  onPress={() => navigation.navigate('RestaurantPlans', { restaurant })}
                >
                  <Image source={{ uri: branchImageUri(restaurant) }} style={styles.restaurantImage} />
                  <View style={styles.restaurantInfo}>
                    <Text style={styles.restaurantName}>{restaurant.name}</Text>
                    <Text style={styles.restaurantMeta} numberOfLines={1}>
                      {restaurant.address || 'Restaurante socio'}
                    </Text>
                  </View>
                </Pressable>
              ))}
            </View>
          </View>
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  wrapper: { width: '100%', maxWidth: 1180, alignSelf: 'center', paddingHorizontal: 16 },
  header: { paddingTop: 8, paddingBottom: 12 },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  greeting: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.extraLarge, color: customerColors.text },
  locationRow: { flexDirection: 'row', alignItems: 'center', marginTop: 4 },
  location: { flexShrink: 1, marginLeft: 4, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
  headerIcons: { flexDirection: 'row', alignItems: 'center' },
  iconButton: { marginLeft: 14 },
  badge: {
    position: 'absolute',
    top: -4,
    right: -6,
    backgroundColor: customerColors.primary,
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  badgeText: { color: '#fff', fontSize: 10, fontFamily: customerFonts.semiBold },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
    backgroundColor: customerColors.offWhite,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    paddingHorizontal: 14,
    height: 46,
  },
  searchPlaceholder: { marginLeft: 8, fontFamily: customerFonts.regular, color: customerColors.hint },
  errorText: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginBottom: 12 },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 24,
    marginBottom: 14,
  },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text },
  sectionLink: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.small, color: customerColors.primary },
  categoryGrid: { flexDirection: 'row', flexWrap: 'wrap' },
  categoryItem: { alignItems: 'center', width: 78, marginRight: 12, marginBottom: 16 },
  categoryIconWrap: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
  },
  categoryLabel: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.text, textAlign: 'center' },
  grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  restaurantCard: {
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
  restaurantImage: { width: '100%', height: 110 },
  restaurantInfo: { padding: 12 },
  restaurantName: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  restaurantMeta: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 4 },
});
