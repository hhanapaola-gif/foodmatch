import React, { useCallback, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useFocusEffect } from '@react-navigation/native';
import Header from '../../components/customer/Header';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { getWalletTransactions } from '../../api/wallet';
import { getCustomerInfo } from '../../api/customer';

const TYPE_LABELS = {
  add_fund: 'Recarga de saldo',
  add_fund_by_admin: 'Recarga por administrador',
  add_fund_bonus: 'Bono de recarga',
  loyalty_point_to_wallet: 'Puntos convertidos',
  referral_order_place: 'Bono por referido',
  order_place: 'Pago de pedido',
  order_refund: 'Reembolso de pedido',
  signup_bonus: 'Bono de bienvenida',
};

export default function WalletScreen() {
  const [balance, setBalance] = useState(0);
  const [transactions, setTransactions] = useState([]);
  const [loading, setLoading] = useState(true);

  useFocusEffect(
    useCallback(() => {
      Promise.all([getCustomerInfo(), getWalletTransactions()])
        .then(([info, wallet]) => {
          setBalance(Number(info?.wallet_balance || 0));
          setTransactions(wallet?.data || []);
        })
        .catch(() => {})
        .finally(() => setLoading(false));
    }, [])
  );

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

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : (
        <FlatList
          data={transactions}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.wrapper}
          ListHeaderComponent={<Text style={styles.sectionTitle}>Movimientos</Text>}
          ListEmptyComponent={<EmptyState icon="wallet-outline" title="Sin movimientos" />}
          renderItem={({ item }) => {
            const isCredit = Number(item.credit) > 0;
            const amount = isCredit ? Number(item.credit) : Number(item.debit);
            return (
              <View style={styles.row}>
                <View style={[styles.iconWrap, { backgroundColor: isCredit ? `${customerColors.secondary}1A` : `${customerColors.error}1A` }]}>
                  <Ionicons
                    name={isCredit ? 'arrow-down-outline' : 'arrow-up-outline'}
                    size={16}
                    color={isCredit ? customerColors.secondary : customerColors.error}
                  />
                </View>
                <View style={styles.info}>
                  <Text style={styles.title}>{TYPE_LABELS[item.transaction_type] || item.transaction_type}</Text>
                  <Text style={styles.date}>{item.created_at?.slice(0, 10)}</Text>
                </View>
                <Text style={[styles.amount, { color: isCredit ? customerColors.secondary : customerColors.error }]}>
                  {isCredit ? '+' : '-'}${amount.toFixed(2)}
                </Text>
              </View>
            );
          }}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
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
