import client from './client';

// CustomerController::info — requires auth:api.
export const getCustomerInfo = () => client.get('/customer/info').then((res) => res.data);

export const updateCustomerProfile = ({ firstName, lastName, email, phone, password }) => {
  const form = new FormData();
  form.append('f_name', firstName);
  if (lastName) form.append('l_name', lastName);
  form.append('email', email);
  form.append('phone', phone);
  if (password) form.append('password', password);
  return client
    .put('/customer/update-profile', form, { headers: { 'Content-Type': 'multipart/form-data' } })
    .then((res) => res.data);
};

export const listAddresses = () => client.get('/customer/address/list').then((res) => res.data);

export const addAddress = ({ label, contactName, contactPhone, address, isDefault, latitude, longitude }) =>
  client
    .post('/customer/address/add', {
      address_type: label,
      contact_person_name: contactName,
      contact_person_number: contactPhone,
      address,
      is_default: isDefault ? 1 : undefined,
      latitude,
      longitude,
    })
    .then((res) => res.data);

export const updateAddress = (id, { label, contactName, contactPhone, address, isDefault, latitude, longitude }) =>
  client
    .put(`/customer/address/update/${id}`, {
      address_type: label,
      contact_person_name: contactName,
      contact_person_number: contactPhone,
      address,
      is_default: isDefault ? 1 : undefined,
      latitude,
      longitude,
    })
    .then((res) => res.data);

export const deleteAddress = (addressId) =>
  client.delete('/customer/address/delete', { data: { address_id: addressId } }).then((res) => res.data);
