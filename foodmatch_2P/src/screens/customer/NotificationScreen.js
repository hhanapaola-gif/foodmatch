import React, { useCallback, useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import Header from '../../components/customer/Header';
import EmptyState from '../../components/customer/EmptyState';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { listNotifications } from '../../api/notifications';

function formatNotificationDate(value) {
  if (!value) return '';
  return new Date(value).toLocaleString('es-MX', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

export default function NotificationScreen() {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState('');

  const load = useCallback(async () => {
    setError('');
    try {
      const data = await listNotifications();
      setNotifications(data || []);
    } catch {
      setError('No pudimos cargar tus notificaciones. Desliza para reintentar.');
    }
  }, []);

  useEffect(() => {
    setLoading(true);
    load().finally(() => setLoading(false));
  }, [load]);

  const onRefresh = async () => {
    setRefreshing(true);
    await load();
    setRefreshing(false);
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <Header title="Notificaciones" />
      {loading ? (
        <ActivityIndicator style={styles.loader} color={customerColors.primary} />
      ) : (
        <FlatList
          data={notifications}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.wrapper}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={customerColors.primary} />}
          ListEmptyComponent={
            <EmptyState icon="notifications-outline" title={error || 'Sin notificaciones'} />
          }
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.iconWrap}>
                <Ionicons name="notifications-outline" size={18} color={customerColors.primary} />
              </View>
              <View style={styles.info}>
                <Text style={styles.title}>{item.title}</Text>
                {!!item.description && <Text style={styles.body}>{item.description}</Text>}
                <Text style={styles.date}>{formatNotificationDate(item.created_at)}</Text>
              </View>
            </View>
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  wrapper: { padding: 16, maxWidth: 700, width: '100%', alignSelf: 'center' },
  card: {
    flexDirection: 'row',
    backgroundColor: customerColors.card,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    padding: 14,
    marginBottom: 12,
  },
  loader: { flex: 1 },
  iconWrap: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: `${customerColors.primary}14`,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  info: { flex: 1 },
  title: { fontFamily: customerFonts.semiBold, fontSize: customerFontSizes.default, color: customerColors.text },
  body: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.small, color: customerColors.hint, marginTop: 2 },
  date: { fontFamily: customerFonts.regular, fontSize: customerFontSizes.extraSmall, color: customerColors.disabled, marginTop: 6 },
});
