import apiClient from './apiClient';

export const scheduleService = {
  async list(params = {}) {
    const { data } = await apiClient.get('/schedules', { params });
    return data;
  },

  async create(payload) {
    const { data } = await apiClient.post('/schedules', payload);
    return data.data;
  },

  async update(id, payload) {
    const { data } = await apiClient.patch(`/schedules/${id}`, payload);
    return data.data;
  },

  async retry(id) {
    const { data } = await apiClient.post(`/schedules/${id}/retry`);
    return data.data;
  },

  async remove(id) {
    const { data } = await apiClient.delete(`/schedules/${id}`);
    return data;
  },
};
