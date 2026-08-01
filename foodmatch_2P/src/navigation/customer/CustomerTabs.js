import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { customerColors, customerFonts, customerFontSizes } from '../../theme/customerTheme';

import HomeScreen from '../../screens/customer/HomeScreen';
import WishlistScreen from '../../screens/customer/WishlistScreen';
import MyPlansScreen from '../../screens/customer/MyPlansScreen';
import MenuScreen from '../../screens/customer/MenuScreen';

const Tab = createBottomTabNavigator();

const ICONS = {
  Inicio: 'home',
  Guardados: 'heart',
  'Mis planes': 'bag-handle',
  Menú: 'grid',
};

export default function CustomerTabs() {
  const insets = useSafeAreaInsets();

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarActiveTintColor: customerColors.primary,
        tabBarInactiveTintColor: customerColors.hint,
        tabBarLabelStyle: { fontFamily: customerFonts.medium, fontSize: customerFontSizes.extraSmall },
        tabBarStyle: { height: 60 + insets.bottom, paddingBottom: insets.bottom + 8, paddingTop: 6 },
        tabBarIcon: ({ color, size, focused }) => (
          <Ionicons name={`${ICONS[route.name]}${focused ? '' : '-outline'}`} color={color} size={size} />
        ),
      })}
    >
      <Tab.Screen name="Inicio" component={HomeScreen} />
      <Tab.Screen name="Guardados" component={WishlistScreen} />
      <Tab.Screen name="Mis planes" component={MyPlansScreen} />
      <Tab.Screen name="Menú" component={MenuScreen} />
    </Tab.Navigator>
  );
}
