import React, { useEffect, useState } from 'react';
import { ActivityIndicator, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import Header from '../../components/customer/Header';
import Input from '../../components/customer/Input';
import Button from '../../components/customer/Button';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useCart } from '../../context/customer/CartContext';
import { subscribeToPlan } from '../../api/plans';
import { extractErrorMessage } from '../../api/client';
import { getCustomerInfo } from '../../api/customer';
import { nextMonday, toISODate, formatDateEs } from '../../utils/plan';

export default function PaymentScreen({ navigation, route }) {
  const { addressText, paymentMethod = 'cash' } = route.params || {};
  const { items, subtotal, removeFromCart } = useCart();
  const [cardNumber, setCardNumber] = useState('');
  const [expiry, setExpiry] = useState('');
  const [cvv, setCvv] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState('');
  const [walletBalance, setWalletBalance] = useState(null);

  const startDate = toISODate(nextMonday());
  const isCard = paymentMethod === 'card';
  const isWallet = paymentMethod === 'wallet';

  useEffect(() => {
    if (isWallet) {
      getCustomerInfo()
        .then((info) => setWalletBalance(Number(info?.wallet_balance || 0)))
        .catch(() => setWalletBalance(0));
    }
  }, [isWallet]);

  const insufficientWallet = isWallet && walletBalance !== null && walletBalance < subtotal;

  const CARD_DIGITS = 16;

  const handleCardNumberChange = (text) => {
    const digits = text.replace(/\D/g, '').slice(0, CARD_DIGITS);
    setCardNumber(digits.match(/.{1,4}/g)?.join(' ') || digits);
  };

  const handleExpiryChange = (text) => {
    // number-pad keyboards have no "/" key, so insert it automatically as
    // the user types (MM then AA) instead of requiring them to type it.
    const digits = text.replace(/\D/g, '').slice(0, 4);
    setExpiry(digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits);
  };

  const handleCvvChange = (text) => {
    setCvv(text.replace(/\D/g, '').slice(0, 3));
  };

  const validateCard = () => {
    const errs = {};

    const cardDigits = cardNumber.replace(/\s/g, '');
    if (cardDigits.length !== CARD_DIGITS) errs.cardNumber = `El número de tarjeta debe tener ${CARD_DIGITS} dígitos`;

    const expiryMatch = /^(\d{2})\/(\d{2})$/.exec(expiry);
    if (!expiryMatch) {
      errs.expiry = 'Formato MM/AA';
    } else {
      const month = Number(expiryMatch[1]);
      const year = 2000 + Number(expiryMatch[2]);
      const now = new Date();
      if (month < 1 || month > 12) {
        errs.expiry = 'Mes inválido';
      } else if (year < now.getFullYear() || (year === now.getFullYear() && month < now.getMonth() + 1)) {
        errs.expiry = 'Tarjeta vencida';
      }
    }

    if (cvv.length !== 3) errs.cvv = 'El CVV debe tener 3 dígitos';

    setFieldErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const paymentLabel = isCard ? 'Tarjeta' : isWallet ? 'Monedero' : 'Efectivo';

  const handlePay = async () => {
    if (isCard && !validateCard()) return;
    if (insufficientWallet) return;

    setProcessing(true);
    setError('');

    const results = await Promise.allSettled(
      items.map((item) =>
        subscribeToPlan(item.plan.id, {
          startDate,
          selectedDays: item.config.selectedDays,
          meals: item.config.meals,
          address: addressText,
          notes: `Método de pago: ${paymentLabel}`,
          paymentMethod,
        }).then(() => item.plan.id)
      )
    );

    const succeededIds = results.filter((r) => r.status === 'fulfilled').map((r) => r.value);
    const failed = results.filter((r) => r.status === 'rejected');

    succeededIds.forEach(removeFromCart);
    setProcessing(false);

    if (failed.length > 0) {
      setError(
        `${succeededIds.length} de ${items.length} planes se confirmaron. ` +
          extractErrorMessage(failed[0].reason, 'Algunos planes no pudieron confirmarse, revisa tu carrito.')
      );
      return;
    }

    navigation.replace('OrderSuccessful');
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Pago" />
      <ScrollView contentContainerStyle={styles.wrapper}>
        <Text style={styles.note}>
          Tu plan comenzará el <Text style={styles.noteStrong}>{formatDateEs(nextMonday())}</Text> (los planes solo pueden
          iniciar la próxima semana).
        </Text>

        {isCard ? (
          <>
            <Text style={styles.label}>Datos de la tarjeta</Text>
            <Input
              icon="card-outline"
              placeholder="Número de tarjeta"
              keyboardType="number-pad"
              value={cardNumber}
              onChangeText={handleCardNumberChange}
              maxLength={19}
              style={styles.field}
            />
            {!!fieldErrors.cardNumber && <Text style={styles.fieldError}>{fieldErrors.cardNumber}</Text>}
            <View style={styles.row}>
              <View style={styles.half}>
                <Input
                  placeholder="MM/AA"
                  keyboardType="number-pad"
                  value={expiry}
                  onChangeText={handleExpiryChange}
                  maxLength={5}
                  style={styles.field}
                />
                {!!fieldErrors.expiry && <Text style={styles.fieldError}>{fieldErrors.expiry}</Text>}
              </View>
              <View style={styles.half}>
                <Input
                  placeholder="CVV"
                  keyboardType="number-pad"
                  secureTextEntry
                  value={cvv}
                  onChangeText={handleCvvChange}
                  maxLength={3}
                  style={styles.field}
                />
                {!!fieldErrors.cvv && <Text style={styles.fieldError}>{fieldErrors.cvv}</Text>}
              </View>
            </View>
            <Text style={styles.note}>
              Este es un pago de demostración: el pedido se confirma directamente en el servidor sin cargar una tarjeta real.
            </Text>
          </>
        ) : isWallet ? (
          <View style={styles.cashCard}>
            <Ionicons name="wallet-outline" size={28} color={customerColors.primary} />
            {walletBalance === null ? (
              <ActivityIndicator color={customerColors.primary} style={{ marginLeft: 14 }} />
            ) : (
              <View style={{ flex: 1, marginLeft: 14 }}>
                <Text style={styles.cashText}>Saldo disponible: ${walletBalance.toFixed(2)}</Text>
                <Text style={styles.cashText}>Total a pagar: ${subtotal.toFixed(2)}</Text>
                {insufficientWallet && (
                  <Text style={[styles.fieldError, { marginTop: 4 }]}>Saldo insuficiente para pagar con el monedero.</Text>
                )}
              </View>
            )}
          </View>
        ) : (
          <View style={styles.cashCard}>
            <Ionicons name="cash-outline" size={28} color={customerColors.primary} />
            <Text style={styles.cashText}>Pagarás en efectivo al momento de recibir tu primera entrega.</Text>
          </View>
        )}

        {!!error && <Text style={styles.errorText}>{error}</Text>}
      </ScrollView>
      <View style={styles.footer}>
        <View style={styles.footerWrapper}>
          <Button
            title="Confirmar suscripción"
            onPress={handlePay}
            loading={processing}
            disabled={items.length === 0 || insufficientWallet || (isWallet && walletBalance === null)}
          />
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { flexGrow: 1, padding: 16, maxWidth: 600, width: '100%', alignSelf: 'center' },
  label: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.large, color: customerColors.text, marginBottom: 16 },
  field: { marginBottom: 4 },
  fieldError: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginBottom: 12 },
  row: { flexDirection: 'row', justifyContent: 'space-between' },
  half: { width: '48%' },
  note: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginBottom: 16, lineHeight: 18 },
  noteStrong: { fontFamily: customerFonts.semiBold, color: customerColors.text },
  cashCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: `${customerColors.primary}0D`,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: `${customerColors.primary}33`,
    padding: 16,
    marginBottom: 8,
  },
  cashText: { flex: 1, marginLeft: 14, fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.text, lineHeight: 20 },
  errorText: { color: customerColors.error, fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, marginTop: 8 },
  footer: { padding: 16, borderTopWidth: 1, borderTopColor: customerColors.border },
  footerWrapper: { maxWidth: 600, width: '100%', alignSelf: 'center' },
});
