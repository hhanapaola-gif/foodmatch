import client from './client';

export const savePlan = (planId) => client.post(`/plans/${planId}/save`).then((res) => res.data);

export const unsavePlan = (planId) => client.delete(`/plans/${planId}/unsave`).then((res) => res.data);

export const listSavedPlans = () => client.get('/customer/saved-plans').then((res) => res.data);

export const listSavedPlanIds = () => client.get('/customer/saved-plan-ids').then((res) => res.data);
