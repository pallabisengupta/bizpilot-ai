import apiClient from './apiClient';

export const leadService = {
  async stats() {
    const { data } = await apiClient.get('/leads/stats');
    return data.data;
  },

  async list(params = {}) {
    const { data } = await apiClient.get('/leads', { params });
    return data;
  },

  async create(payload) {
    const { data } = await apiClient.post('/leads', payload);
    return data.data;
  },

  async update(id, payload) {
    const { data } = await apiClient.patch(`/leads/${id}`, payload);
    return data.data;
  },

  async assign(id, assignedUserId) {
    const { data } = await apiClient.post(`/leads/${id}/assign`, {
      assigned_user_id: assignedUserId || null,
    });
    return data.data;
  },

  async addNote(id, note) {
    const { data } = await apiClient.post(`/leads/${id}/notes`, { note });
    return data.data;
  },

  async activities(id) {
    const { data } = await apiClient.get(`/leads/${id}/activities`);
    return data;
  },
};
