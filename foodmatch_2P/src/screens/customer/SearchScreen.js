import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, TextInput, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import { customerColors, customerFonts, customerFontSizes, customerRadii } from '../../theme/customerTheme';
import { useColumnCount } from '../../utils/responsive';
import PlanCard from '../../components/customer/PlanCard';
import EmptyState from '../../components/customer/EmptyState';
import { listPlans } from '../../api/plans';

export default function SearchScreen() {
  const navigation = useNavigation();
  const [query, setQuery] = useState('');
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const columns = useColumnCount({ base: 2, tablet: 3, desktop: 4, wide: 4 });

  useEffect(() => {
    // The API has no text-search endpoint for plans, so we fetch the
    // catalog once and filter client-side.
    listPlans({ limit: 100 })
      .then((data) => setPlans(data?.plans || []))
      .catch(() => setPlans([]))
      .finally(() => setLoading(false));
  }, []);

  const results = useMemo(() => {
    if (!query.trim()) return [];
    const q = query.trim().toLowerCase();
    return plans.filter(
      (p) =>
        p.title.toLowerCase().includes(q) ||
        p.branch?.name?.toLowerCase().includes(q) ||
        p.category_name?.toLowerCase().includes(q)
    );
  }, [query, plans]);

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <View style={styles.searchBar}>
        <Ionicons name="chevron-back" size={22} color={customerColors.text} onPress={() => navigation.goBack()} />
        <View style={styles.inputWrap}>
          <Ionicons name="search-outline" size={18} color={customerColors.hint} />
          <TextInput
            autoFocus
            value={query}
            onChangeText={setQuery}
            placeholder="Busca un plan o restaurante"
            placeholderTextColor={customerColors.hint}
            style={styles.input}
          />
        </View>
      </View>

      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={customerColors.primary} />
        </View>
      ) : query.trim() === '' ? (
        <EmptyState icon="search-outline" title="Busca tu plan ideal" message="Escribe el nombre de un plan o restaurante." />
      ) : (
        <FlatList
          data={results}
          keyExtractor={(item) => String(item.id)}
          numColumns={columns}
          key={columns}
          contentContainerStyle={styles.list}
          columnWrapperStyle={columns > 1 ? styles.row : undefined}
          ListEmptyComponent={<EmptyState icon="sad-outline" title="Sin resultados" message={`No encontramos nada para "${query}"`} />}
          renderItem={({ item }) => (
            <PlanCard plan={item} style={{ width: `${100 / columns - 2}%`, marginBottom: 16 }} />
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  center: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  searchBar: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 12 },
  inputWrap: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    marginLeft: 12,
    backgroundColor: customerColors.offWhite,
    borderRadius: customerRadii.default,
    borderWidth: 1,
    borderColor: customerColors.border,
    paddingHorizontal: 12,
    height: 44,
  },
  input: { flex: 1, marginLeft: 8, fontFamily: customerFonts.regular, fontSize: customerFontSizes.default, color: customerColors.text },
  list: { padding: 16, maxWidth: 1180, width: '100%', alignSelf: 'center' },
  row: { justifyContent: 'space-between' },
});
