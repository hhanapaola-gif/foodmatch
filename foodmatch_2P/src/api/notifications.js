import client from './client';

export const listNotifications = () => client.get('/notifications').then((res) => res.data);
