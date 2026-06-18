import apiClient from './apiClient';

export const adminService = {
  async analytics() {
    const { data } = await apiClient.get('/admin/analytics');
    return data.data;
  },

  async plans() {
    const { data } = await apiClient.get('/admin/plans');
    return data;
  },

  async products() {
    const { data } = await apiClient.get('/admin/products');
    return data;
  },

  async createProduct(payload) {
    const { data } = await apiClient.post('/admin/products', payload);
    return data.data.product;
  },

  async planFeatures() {
    const { data } = await apiClient.get('/admin/plan-features');
    return data;
  },

  async createPlanFeature(payload) {
    const { data } = await apiClient.post('/admin/plan-features', payload);
    return data.data.feature;
  },

  async createPlan(payload) {
    const { data } = await apiClient.post('/admin/plans', payload);
    return data.data;
  },

  async tenants(params = {}) {
    const { data } = await apiClient.get('/admin/tenants', { params });
    return data;
  },

  async subscriptions() {
    const { data } = await apiClient.get('/admin/subscriptions');
    return data;
  },

  async markSubscriptionPaid(id) {
    const { data } = await apiClient.post(`/admin/subscriptions/${id}/mark-paid`);
    return data.data;
  },

  async cancelSubscription(id) {
    const { data } = await apiClient.post(`/admin/subscriptions/${id}/cancel`);
    return data.data;
  },

  async activateTenant(id) {
    const { data } = await apiClient.post(`/admin/tenants/${id}/activate`);
    return data.data;
  },

  async suspendTenant(id) {
    const { data } = await apiClient.post(`/admin/tenants/${id}/suspend`);
    return data.data;
  },
};
