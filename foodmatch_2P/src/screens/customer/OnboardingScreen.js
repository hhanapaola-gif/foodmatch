import React, { useRef, useState } from 'react';
import { FlatList, Image, StyleSheet, Text, View, useWindowDimensions } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';
import Button from '../../components/customer/Button';

const SLIDES = [
  {
    id: '1',
    title: 'Planes alimenticios de varios restaurantes',
    text: 'Descubre planes semanales y mensuales creados por distintos restaurantes socios, todo en un solo lugar.',
    image: 'https://picsum.photos/seed/onboard1/600/500',
  },
  {
    id: '2',
    title: 'Suscríbete a tu plan ideal',
    text: 'Elige tu tipo de dieta, la frecuencia de entrega y recibe tus comidas sin complicarte cada semana.',
    image: 'https://picsum.photos/seed/onboard2/600/500',
  },
  {
    id: '3',
    title: 'Sigue tu calendario de entregas',
    text: 'Consulta el calendario con todas tus entregas programadas y el estado de cada una.',
    image: 'https://picsum.photos/seed/onboard3/600/500',
  },
];

export default function OnboardingScreen({ navigation }) {
  const { width } = useWindowDimensions();
  const [index, setIndex] = useState(0);
  const listRef = useRef(null);

  const next = () => {
    if (index < SLIDES.length - 1) {
      listRef.current?.scrollToIndex({ index: index + 1 });
      setIndex(index + 1);
    } else {
      navigation.replace('Welcome');
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <FlatList
        ref={listRef}
        data={SLIDES}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        keyExtractor={(item) => item.id}
        onMomentumScrollEnd={(e) => setIndex(Math.round(e.nativeEvent.contentOffset.x / width))}
        renderItem={({ item }) => (
          <View style={[styles.slide, { width }]}>
            <Image source={{ uri: item.image }} style={styles.image} />
            <Text style={styles.title}>{item.title}</Text>
            <Text style={styles.text}>{item.text}</Text>
          </View>
        )}
      />
      <View style={styles.dots}>
        {SLIDES.map((s, i) => (
          <View key={s.id} style={[styles.dot, i === index && styles.dotActive]} />
        ))}
      </View>
      <View style={styles.footer}>
        <Button title={index === SLIDES.length - 1 ? 'Comenzar' : 'Siguiente'} onPress={next} />
        {index < SLIDES.length - 1 && (
          <Button title="Omitir" variant="text" onPress={() => navigation.replace('Welcome')} />
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: customerColors.background },
  slide: { alignItems: 'center', paddingHorizontal: 28, paddingTop: 60 },
  image: { width: '100%', height: 320, borderRadius: 24, marginBottom: 32 },
  title: {
    fontFamily: customerFonts.bold,
    fontSize: customerFontSizes.overLarge,
    color: customerColors.text,
    textAlign: 'center',
    marginBottom: 12,
  },
  text: {
    fontFamily: customerFonts.regular,
    fontSize: customerFontSizes.default,
    color: customerColors.hint,
    textAlign: 'center',
  },
  dots: { flexDirection: 'row', justifyContent: 'center', marginTop: 12 },
  dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: customerColors.disabled, marginHorizontal: 4 },
  dotActive: { backgroundColor: customerColors.primary, width: 20 },
  footer: { paddingHorizontal: 24, paddingBottom: 24, paddingTop: 12 },
});
