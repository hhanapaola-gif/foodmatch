import client from './client';

export const listPlans = ({ categoryId, limit = 20, offset = 1 } = {}) =>
  client
    .get('/plans', { params: { category_id: categoryId, limit, offset } })
    .then((res) => res.data);

export const getPlan = (id) => client.get(`/plans/${id}`).then((res) => res.data);

export const listPlansByRestaurant = (branchId) =>
  client.get(`/restaurants/${branchId}/plans`).then((res) => res.data);

// start_date must be an ISO "YYYY-MM-DD" string on/after next Monday.
// selectedDays: ['mon','tue',...]  meals: ['breakfast','lunch','dinner']
export const subscribeToPlan = (planId, { startDate, selectedDays, meals, address, notes, paymentMethod }) =>
  client
    .post(`/plans/${planId}/order`, {
      start_date: startDate,
      selected_days: selectedDays,
      meals,
      address,
      notes,
      payment_method: paymentMethod,
    })
    .then((res) => res.data);

export const listMyPlanOrders = (status) =>
  client.get('/user/plan-orders', { params: { status } }).then((res) => res.data);

export const cancelPlanOrder = (id) =>
  client.put(`/user/plan-orders/${id}/cancel`).then((res) => res.data);

export const listPlanCategories = () => client.get('/plan-categories').then((res) => res.data);

export const listBranches = () => client.get('/branch/list').then((res) => res.data);

export const getBranch = (id) => client.get(`/branch/${id}`).then((res) => res.data);
