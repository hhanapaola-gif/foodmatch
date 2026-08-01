import React from 'react';
import { Image, Pressable, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useWishlist } from '../../context/customer/WishlistContext';
import { availableMeals } from '../../utils/plan';

const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=60';

export default function PlanCard({ plan, style }) {
  const navigation = useNavigation();
  const { isWishlisted, toggleWishlist } = useWishlist();
  const wished = isWishlisted(plan.id);

  const meals = availableMeals(plan);
  const lowestPrice = meals.length ? Math.min(...meals.map((m) => Number(plan[m.priceField]))) : null;

  return (
    <Pressable style={[styles.card, style]} onPress={() => navigation.navigate('PlanDetail', { plan })}>
      <View>
        <Image source={{ uri: plan.image || PLACEHOLDER_IMAGE }} style={styles.image} />
        <Pressable style={styles.wishlistButton} onPress={() => toggleWishlist(plan)}>
          <Ionicons name={wished ? 'heart' : 'heart-outline'} size={16} color={wished ? customerColors.primary : '#fff'} />
        </Pressable>
      </View>
      <View style={styles.info}>
        <Text style={styles.name} numberOfLines={1}>
          {plan.title}
        </Text>
        <Text style={styles.restaurant} numberOfLines={1}>
          {plan.branch?.name || plan.category_name || ''}
        </Text>
        {lowestPrice !== null && (
          <Text style={styles.price}>
            Desde ${lowestPrice.toFixed(0)} / comida · {plan.type === 'monthly' ? 'Mensual' : 'Semanal'}
          </Text>
        )}
      </View>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOpacity: 0.06,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 3 },
    elevation: 2,
  },
  image: {
    width: '100%',
    height: 120,
  },
  wishlistButton: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: 'rgba(0,0,0,0.35)',
    borderRadius: 20,
    padding: 6,
  },
  info: {
    padding: 12,
  },
  name: {
    fontFamily: customerFonts.semiBold,
    fontSize: customerFontSizes.default,
    color: customerColors.text,
    marginBottom: 2,
  },
  restaurant: {
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.small,
    color: customerColors.hint,
    marginBottom: 4,
  },
  price: {
    marginTop: 6,
    fontFamily: customerFonts.semiBold,
    fontSize: customerFontSizes.small,
    color: customerColors.primary,
  },
});
