import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import SplashScreen from '../../screens/customer/SplashScreen';
import OnboardingScreen from '../../screens/customer/OnboardingScreen';
import WelcomeScreen from '../../screens/customer/WelcomeScreen';
import LoginScreen from '../../screens/customer/LoginScreen';
import CreateAccountScreen from '../../screens/customer/CreateAccountScreen';
import DashboardScreen from '../../screens/customer/DashboardScreen';
import PlanCategoryScreen from '../../screens/customer/PlanCategoryScreen';
import RestaurantListScreen from '../../screens/customer/RestaurantListScreen';
import RestaurantPlansScreen from '../../screens/customer/RestaurantPlansScreen';
import SearchScreen from '../../screens/customer/SearchScreen';
import PlanDetailScreen from '../../screens/customer/PlanDetailScreen';
import CartScreen from '../../screens/customer/CartScreen';
import CheckoutScreen from '../../screens/customer/CheckoutScreen';
import PaymentScreen from '../../screens/customer/PaymentScreen';
import OrderSuccessfulScreen from '../../screens/customer/OrderSuccessfulScreen';
import OrderScreen from '../../screens/customer/OrderScreen';
import OrderDetailsScreen from '../../screens/customer/OrderDetailsScreen';
import PlanCalendarScreen from '../../screens/customer/PlanCalendarScreen';
import ProfileScreen from '../../screens/customer/ProfileScreen';
import AddressScreen from '../../screens/customer/AddressScreen';
import WalletScreen from '../../screens/customer/WalletScreen';
import NotificationScreen from '../../screens/customer/NotificationScreen';

const Stack = createNativeStackNavigator();

export default function CustomerNavigator() {
  return (
    <Stack.Navigator initialRouteName="Splash" screenOptions={{ headerShown: false }}>
      <Stack.Screen name="Splash" component={SplashScreen} />
      <Stack.Screen name="Onboarding" component={OnboardingScreen} />
      <Stack.Screen name="Welcome" component={WelcomeScreen} />
      <Stack.Screen name="Login" component={LoginScreen} />
      <Stack.Screen name="CreateAccount" component={CreateAccountScreen} />
      <Stack.Screen name="Dashboard" component={DashboardScreen} />
      <Stack.Screen name="PlanCategory" component={PlanCategoryScreen} />
      <Stack.Screen name="RestaurantList" component={RestaurantListScreen} />
      <Stack.Screen name="RestaurantPlans" component={RestaurantPlansScreen} />
      <Stack.Screen name="Search" component={SearchScreen} />
      <Stack.Screen name="PlanDetail" component={PlanDetailScreen} />
      <Stack.Screen name="Cart" component={CartScreen} />
      <Stack.Screen name="Checkout" component={CheckoutScreen} />
      <Stack.Screen name="Payment" component={PaymentScreen} />
      <Stack.Screen name="OrderSuccessful" component={OrderSuccessfulScreen} />
      <Stack.Screen name="Order" component={OrderScreen} />
      <Stack.Screen name="OrderDetails" component={OrderDetailsScreen} />
      <Stack.Screen name="PlanCalendar" component={PlanCalendarScreen} />
      <Stack.Screen name="Profile" component={ProfileScreen} />
      <Stack.Screen name="Address" component={AddressScreen} />
      <Stack.Screen name="Wallet" component={WalletScreen} />
      <Stack.Screen name="Notification" component={NotificationScreen} />
    </Stack.Navigator>
  );
}
