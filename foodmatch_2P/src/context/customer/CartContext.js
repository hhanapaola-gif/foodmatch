import React, { createContext, useContext, useMemo, useState } from 'react';

const CartContext = createContext(null);

const MEAL_PRICE_FIELD = {
  breakfast: 'breakfast_price',
  lunch: 'lunch_price',
  dinner: 'dinner_price',
};

// Mirrors PlanController::order's pricing exactly, so what the cart shows
// is what the backend will actually charge when the order is placed.
// Billing cadence comes from plan.type (fixed by the restaurant), never from
// a customer choice — the backend has no per-order frequency parameter.
// config: { selectedDays: string[] (day codes), meals: string[] (meal codes) }
function getPlanPrice(plan, config) {
  const { selectedDays = [], meals = [] } = config || {};
  const dayCount = selectedDays.length;
  const weekly = meals.reduce((sum, meal) => {
    const price = Number(plan[MEAL_PRICE_FIELD[meal]] || 0);
    return sum + price * dayCount;
  }, 0);
  return plan.type === 'monthly' ? weekly * 4 : weekly;
}

export function CartProvider({ children }) {
  const [items, setItems] = useState([]);

  const addToCart = (plan, config) => {
    setItems((prev) => {
      const existing = prev.find((it) => it.plan.id === plan.id);
      if (existing) {
        return prev.map((it) => (it.plan.id === plan.id ? { ...it, config } : it));
      }
      return [...prev, { plan, config }];
    });
  };

  const removeFromCart = (planId) => {
    setItems((prev) => prev.filter((it) => it.plan.id !== planId));
  };

  const clearCart = () => setItems([]);

  const subtotal = useMemo(
    () => items.reduce((sum, it) => sum + getPlanPrice(it.plan, it.config), 0),
    [items]
  );

  const itemCount = items.length;

  const value = useMemo(
    () => ({ items, addToCart, removeFromCart, clearCart, subtotal, itemCount, getPlanPrice }),
    [items, subtotal, itemCount]
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export const useCart = () => useContext(CartContext);

export default CartContext;
