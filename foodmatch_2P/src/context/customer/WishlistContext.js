import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { useAuth } from './AuthContext';
import { listSavedPlanIds, listSavedPlans, savePlan, unsavePlan } from '../../api/savedPlans';

const WishlistContext = createContext(null);

export function WishlistProvider({ children }) {
  const { isAuthenticated } = useAuth();
  const [wishlistPlans, setWishlistPlans] = useState([]);
  const [wishlistIds, setWishlistIds] = useState(new Set());

  const refreshWishlist = async () => {
    if (!isAuthenticated) {
      setWishlistPlans([]);
      setWishlistIds(new Set());
      return;
    }
    const [{ saved_plans: plans }, { plan_ids: ids }] = await Promise.all([
      listSavedPlans(),
      listSavedPlanIds(),
    ]);
    setWishlistPlans(plans || []);
    setWishlistIds(new Set(ids || []));
  };

  useEffect(() => {
    refreshWishlist().catch(() => {});
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAuthenticated]);

  const isWishlisted = (planId) => wishlistIds.has(planId);

  const toggleWishlist = async (plan) => {
    if (!isAuthenticated) return;
    const wished = wishlistIds.has(plan.id);
    // optimistic update so the heart icon responds instantly
    setWishlistIds((prev) => {
      const next = new Set(prev);
      wished ? next.delete(plan.id) : next.add(plan.id);
      return next;
    });
    setWishlistPlans((prev) => (wished ? prev.filter((p) => p.id !== plan.id) : [plan, ...prev]));

    try {
      wished ? await unsavePlan(plan.id) : await savePlan(plan.id);
    } catch {
      await refreshWishlist();
    }
  };

  const value = useMemo(
    () => ({ wishlistPlans, isWishlisted, toggleWishlist, refreshWishlist }),
    [wishlistPlans, wishlistIds]
  );

  return <WishlistContext.Provider value={value}>{children}</WishlistContext.Provider>;
}

export const useWishlist = () => useContext(WishlistContext);

export default WishlistContext;
