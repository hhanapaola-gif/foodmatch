import React from 'react';
import { FlatList, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';

const TRANSACTIONS = [
  { id: 't1', title: 'Cashback suscripción Plan Semanal Balanceado', amount: 12.5, type: 'credit', date: '2026-06-28' },
  { id: 't2', title: 'Reembolso suscripción cancelada', amount: 119.0, type: 'credit', date: '2026-06-20' },
  { id: 't3', title: 'Pago con monedero - Plan Fitness', amount: -50.0, type: 'debit', date: '2026-06-18' },
];

export default function WalletScreen() {
  const balance = TRANSACTIONS.reduce((sum, t) => sum + t.amount, 0);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Monedero" />
      <View style={styles.wrapper}>
        <View style={styles.balanceCard}>
          <Ionicons name="wallet-outline" size={28} color="#fff" />
          <Text style={styles.balanceLabel}>Saldo disponible</Text>
          <Text style={styles.balanceValue}>${balance.toFixed(2)}</Text>
        </View>
      </View>

      <FlatList
        data={TRANSACTIONS}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.wrapper}
        ListHeaderComponent={<Text style={styles.sectionTitle}>Movimientos</Text>}
        ListEmptyComponent={<EmptyState icon="wallet-outline" title="Sin movimientos" />}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <View style={[styles.iconWrap, { backgroundColor: item.type === 'credit' ? `${customerColors.secondary}1A` : `${customerColors.error}1A` }]}>
              <Ionicons
                name={item.type === 'credit' ? 'arrow-down-outline' : 'arrow-up-outline'}
                size={16}
                color={item.type === 'credit' ? customerColors.secondary : customerColors.error}
              />
            </View>
            <View style={styles.info}>
              <Text style={styles.title}>{item.title}</Text>
              <Text style={styles.date}>{item.date}</Text>
            </View>
            <Text style={[styles.amount, { color: item.type === 'credit' ? customerColors.secondary : customerColors.error }]}>
              {item.type === 'credit' ? '+' : ''}${item.amount.toFixed(2)}
            </Text>
          </View>
        )}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { paddingHorizontal: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  balanceCard: {
    marginTop: 16,
    padding: 20,
    borderRadius: customerRadii.large,
    backgroundColor: customerColors.primary,
  },
  balanceLabel: { color: '#fff', opacity: 0.85, fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, marginTop: 10 },
  balanceValue: { color: '#fff', fontFamily: customerFonts.bold, fontSize: customerFontSizes.overLarge + 6, marginTop: 4 },
  sectionTitle: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginTop: 20, marginBottom: 12 },
  row: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
  iconWrap: { width: 36, height: 36, borderRadius: 18, alignItems: 'center', justifyContent: 'center', marginRight: 12 },
  info: { flex: 1 },
  title: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text },
  date: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  amount: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default },
});
