import apiClient from './apiClient';

export const authService = {
  async login(payload) {
    const { data } = await apiClient.post('/auth/login', payload);
    return data.data;
  },

  async checkEmail(email) {
    const { data } = await apiClient.post('/auth/check-email', { email });
    return data.data;
  },

  async register(payload) {
    const { data } = await apiClient.post('/auth/register', payload);
    return data.data;
  },

  async logout() {
    await apiClient.post('/auth/logout');
  },
};
