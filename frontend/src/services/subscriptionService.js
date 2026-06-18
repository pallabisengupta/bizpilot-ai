import apiClient from './apiClient';

export const subscriptionService = {
  async plans() {
    const { data } = await apiClient.get('/plans');
    return data.data || [];
  },

  async products() {
    const { data } = await apiClient.get('/products');
    return data.data || [];
  },

  async productPlans(productUrl) {
    const { data } = await apiClient.get(`/products/${productUrl}/plans`);
    return {
      plans: data.data || [],
      product: data.product || null,
    };
  },

  async current() {
    const { data } = await apiClient.get('/subscription');
    return data.data.subscription;
  },

  async subscribe(planId) {
    const { data } = await apiClient.post('/subscription/subscribe', { plan_id: planId });
    return data.data.subscription;
  },

  async checkout(planId) {
    const { data } = await apiClient.post('/subscription/checkout', { plan_id: planId });
    return data.data.checkout;
  },

  async verify(payload) {
    const { data } = await apiClient.post('/subscription/verify', payload);
    return data.data.subscription;
  },
};
