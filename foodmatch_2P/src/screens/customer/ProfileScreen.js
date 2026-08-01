import React from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import Avatar from '../../components/customer/Avatar';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useAuth } from '../../context/customer/AuthContext';

const MENU = [
  { key: 'Order', label: 'Mis pedidos', icon: 'receipt-outline' },
  { key: 'PlanCalendar', label: 'Calendario de entregas', icon: 'calendar-outline' },
  { key: 'Address', label: 'Mis direcciones', icon: 'location-outline' },
  { key: 'Wallet', label: 'Monedero', icon: 'wallet-outline' },
  { key: 'Notification', label: 'Notificaciones', icon: 'notifications-outline' },
];

export default function ProfileScreen({ navigation }) {
  const { user, isGuest, logout } = useAuth();

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Perfil" />
      <ScrollView contentContainerStyle={styles.wrapper}>
        <View style={styles.profileHeader}>
          <Avatar name={user?.name || (isGuest ? 'Invitado' : 'Usuario')} size={68} />
          <View style={{ marginLeft: 14 }}>
            <Text style={styles.name}>{user?.name || (isGuest ? 'Invitado' : 'Usuario')}</Text>
            <Text style={styles.email}>{user?.email || 'Inicia sesión para más funciones'}</Text>
          </View>
        </View>

        <View style={styles.menu}>
          {MENU.map((item) => (
            <Pressable key={item.key} style={styles.menuItem} onPress={() => navigation.navigate(item.key)}>
              <Ionicons name={item.icon} size={20} color={customerColors.primary} />
              <Text style={styles.menuLabel}>{item.label}</Text>
              <Ionicons name="chevron-forward" size={18} color={customerColors.hint} />
            </Pressable>
          ))}
        </View>

        {user && (
          <Pressable
            style={styles.logout}
            onPress={async () => {
              await logout();
              navigation.reset({ index: 0, routes: [{ name: 'Welcome' }] });
            }}
          >
            <Ionicons name="log-out-outline" size={20} color={customerColors.error} />
            <Text style={styles.logoutLabel}>Cerrar sesión</Text>
          </Pressable>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { padding: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  profileHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 24 },
  name:{ fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.extraLarge, color: customerColors.text },
  email: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  menu: {
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    overflow: 'hidden',
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: customerColors.border,
  },
  menuLabel: { flex: 1, marginLeft: 14, fontFamily: customerFonts.medium, fontSize: customerFontSizes.default, color: customerColors.text },
  logout: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginTop: 24, padding: 12 },
  logoutLabel: { marginLeft: 8, fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.error },
});
