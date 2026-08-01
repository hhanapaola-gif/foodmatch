import client from './client';

export const getWalletTransactions = ({ limit = 20, offset = 1 } = {}) =>
  client.get('/customer/wallet-transactions', { params: { limit, offset } }).then((res) => res.data);
