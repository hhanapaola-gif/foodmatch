import React, { useState } from 'react';
import { Image, Linking, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import Header from '../../components/customer/Header';
import Button from '../../components/customer/Button';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useCart } from '../../context/customer/CartContext';
import { availableDays, availableMeals } from '../../utils/plan';
import { SERVER_BASE_URL } from '../../config/env';

const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=900&q=60';

export default function PlanDetailScreen({ route, navigation }) {
  const { plan } = route.params || {};
  const { addToCart, getPlanPrice } = useCart();
  const meals = plan ? availableMeals(plan) : [];
  const days = plan ? availableDays(plan) : [];

  const [selectedDays, setSelectedDays] = useState(() => days.map((d) => d.code));
  const [selectedMeals, setSelectedMeals] = useState(meals.map((m) => m.code));
  const [formError, setFormError] = useState('');

  if (!plan) return null;

  // Billing cadence (weekly/monthly) is fixed per plan by the restaurant that
  // created it — PlanController::order prices and stores it from plan.type,
  // it does not accept a customer-chosen frequency. Never offer it as a toggle.
  const isMonthly = plan.type === 'monthly';
  const total = getPlanPrice(plan, { selectedDays, meals: selectedMeals });
  const menuPdfUrl = plan.menu_pdf ? `${SERVER_BASE_URL}/${plan.menu_pdf}` : null;

  const handleViewMenu = () => {
    if (menuPdfUrl) Linking.openURL(menuPdfUrl);
  };

  const toggleDay = (code) => {
    setSelectedDays((prev) => (prev.includes(code) ? prev.filter((d) => d !== code) : [...prev, code]));
  };

  const toggleMeal = (code) => {
    setSelectedMeals((prev) => (prev.includes(code) ? prev.filter((m) => m !== code) : [...prev, code]));
  };

  const handleAddToCart = () => {
    if (selectedDays.length === 0) {
      setFormError('Selecciona al menos un día de entrega.');
      return;
    }
    if (selectedMeals.length === 0) {
      setFormError('Selecciona al menos un tipo de comida.');
      return;
    }
    setFormError('');
    addToCart(plan, { selectedDays, meals: selectedMeals });
    navigation.navigate('Cart');
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Detalle del plan" />
      <ScrollView>
        <View style={styles.wrapper}>
          <Image source={{ uri: plan.image || PLACEHOLDER_IMAGE }} style={styles.image} />
          <View style={styles.content}>
            <Text style={styles.title}>{plan.title}</Text>
            <Text style={styles.restaurant}>{plan.branch?.name || plan.category_name}</Text>

            <View style={styles.tagsRow}>
              {!!plan.category_name && (
                <View style={styles.tag}>
                  <Text style={styles.tagText}>{plan.category_name}</Text>
                </View>
              )}
              <View style={[styles.tag, styles.tagCadence]}>
                <Text style={[styles.tagText, styles.tagCadenceText]}>
                  {isMonthly ? 'Suscripción mensual' : 'Suscripción semanal'}
                </Text>
              </View>
            </View>

            {!!plan.description && <Text style={styles.description}>{plan.description}</Text>}

            {!!menuPdfUrl && (
              <Pressable style={styles.menuButton} onPress={handleViewMenu}>
                <Ionicons name="document-text-outline" size={18} color={customerColors.primary} />
                <Text style={styles.menuButtonText}>Ver menú del restaurante (PDF)</Text>
              </Pressable>
            )}

            <Text style={styles.sectionTitle}>Comidas incluidas</Text>
            <View style={styles.daysRow}>
              {meals.map((meal) => {
                const selected = selectedMeals.includes(meal.code);
                return (
                  <Pressable
                    key={meal.code}
                    style={[styles.mealChip, selected && styles.dayChipActive]}
                    onPress={() => toggleMeal(meal.code)}
                  >
                    <Text style={[styles.dayChipText, selected && styles.dayChipTextActive]}>{meal.label}</Text>
                  </Pressable>
                );
              })}
            </View>

            <Text style={styles.sectionTitle}>Días de entrega</Text>
            <View style={styles.daysRow}>
              {days.map((day) => {
                const selected = selectedDays.includes(day.code);
                return (
                  <Pressable
                    key={day.code}
                    style={[styles.dayChip, selected && styles.dayChipActive]}
                    onPress={() => toggleDay(day.code)}
                  >
                    <Text style={[styles.dayChipText, selected && styles.dayChipTextActive]}>{day.label}</Text>
                  </Pressable>
                );
              })}
            </View>

            {!!formError && <Text style={styles.formError}>{formError}</Text>}
          </View>
        </View>
      </ScrollView>
      <View style={styles.footer}>
        <View style={styles.footerWrapper}>
          <View style={styles.footerPrice}>
            <Text style={styles.footerLabel}>Total {isMonthly ? 'mensual' : 'semanal'}</Text>
            <Text style={styles.footerTotal}>${total.toFixed(0)}</Text>
          </View>
          <Button title="Agregar al carrito" style={styles.footerButton} onPress={handleAddToCart} />
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { width: '100%', maxWidth: 900, alignSelf: 'center' },
  image: { width: '100%', height: 260 },
  content: { padding: 20 },
  title: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.overLarge, color: customerColors.text },
  restaurant: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.hint, marginTop: 4, marginBottom: 8 },
  tagsRow: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 12 },
  tag: { backgroundColor: `${customerColors.secondary}1A`, borderRadius: 14, paddingHorizontal: 12, paddingVertical: 5, marginRight: 8, marginBottom: 8 },
  tagText: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.small, color: customerColors.secondary },
  tagCadence: { backgroundColor: `${customerColors.primary}14` },
  tagCadenceText: { color: customerColors.primary },
  description: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.text, lineHeight: 20, marginTop: 6, marginBottom: 8 },
  menuButton: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: `${customerColors.primary}14`,
    borderRadius: customerRadii.default,
    paddingHorizontal: 14,
    paddingVertical: 10,
    marginTop: 4,
    marginBottom: 8,
  },
  menuButtonText: { marginLeft: 8, fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.primary },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginTop: 20, marginBottom: 12 },
  daysRow: { flexDirection: 'row', flexWrap: 'wrap' },
  mealChip: {
    height: 40,
    paddingHorizontal: 16,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
    marginBottom: 8,
  },
  dayChip: {
    width: 48,
    height: 40,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
    marginBottom: 8,
  },
  dayChipActive: { backgroundColor: customerColors.primary, borderColor: customerColors.primary },
  dayChipText: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.small, color: customerColors.text },
  dayChipTextActive: { color: '#fff' },
  formError: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginTop: 4 },
  footer: { padding: 16, borderTopWidth: 1, borderTopColor: customerColors.border },
  footerWrapper: {
    width: '100%',
    maxWidth: 900,
    alignSelf: 'center',
    flexDirection: 'row',
    alignItems: 'center',
  },
  footerPrice: { marginRight: 16 },
  footerLabel: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint },
  footerTotal: { fontFamily: customerFonts.bold, fontSize: customerFontSizes.extraLarge, color: customerColors.primary },
  footerButton: { flex: 1 },
});
