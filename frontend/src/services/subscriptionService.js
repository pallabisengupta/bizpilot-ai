import apiClient from './apiClient';

export const subscriptionService = {
  async plans() {
    const { data } = await apiClient.get('/plans');
    return data.data || [];
  },

  async current() {
    const { data } = await apiClient.get('/subscription');
    return data.data.subscription;
  },

  async subscribe(planId) {
    const { data } = await apiClient.post('/subscription/subscribe', { plan_id: planId });
    return data.data.subscription;
  },
};
