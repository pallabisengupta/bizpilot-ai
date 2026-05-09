import apiClient from './apiClient';

export const tenantService = {
  async getOnboarding() {
    const { data } = await apiClient.get('/tenants/onboarding');
    return data.data;
  },

  async saveOnboardingStep(payload) {
    const { data } = await apiClient.post('/tenants/onboarding/step', payload);
    return data.data;
  },

  async completeOnboarding(payload) {
    const { data } = await apiClient.post('/tenants/onboarding', payload);
    return data.data;
  },
};
