import React, { useRef, useState } from 'react';
import { Dimensions, FlatList, Image, Pressable, StyleSheet, View } from 'react-native';
import { customerColors, customerRadii } from '../../theme/customerTheme';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const SLIDE_WIDTH = Math.min(SCREEN_WIDTH, 1180) - 32;

export default function BannerCarousel({ banners, onPressBanner }) {
  const [activeIndex, setActiveIndex] = useState(0);
  const listRef = useRef(null);

  if (!banners || banners.length === 0) return null;

  const onScroll = (e) => {
    const index = Math.round(e.nativeEvent.contentOffset.x / SLIDE_WIDTH);
    setActiveIndex(index);
  };

  return (
    <View style={styles.wrapper}>
      <FlatList
        ref={listRef}
        data={banners}
        horizontal
        pagingEnabled
        showsHorizontalScrollIndicator={false}
        keyExtractor={(item) => String(item.id)}
        onMomentumScrollEnd={onScroll}
        snapToInterval={SLIDE_WIDTH}
        decelerationRate="fast"
        renderItem={({ item }) => (
          <Pressable style={{ width: SLIDE_WIDTH }} onPress={() => onPressBanner?.(item)}>
            <Image source={{ uri: item.image_full_path }} style={styles.image} />
          </Pressable>
        )}
      />
      {banners.length > 1 && (
        <View style={styles.dotsRow}>
          {banners.map((_, i) => (
            <View key={i} style={[styles.dot, i === activeIndex && styles.dotActive]} />
          ))}
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { marginBottom: 8 },
  image: { width: '100%', height: 150, borderRadius: customerRadii.default },
  dotsRow: { flexDirection: 'row', justifyContent: 'center', marginTop: 8 },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: customerColors.border,
    marginHorizontal: 3,
  },
  dotActive: { backgroundColor: customerColors.primary, width: 16 },
});
