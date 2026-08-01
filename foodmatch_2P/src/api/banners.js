import client from './client';

export const listBanners = () => client.get('/banners').then((res) => res.data);
