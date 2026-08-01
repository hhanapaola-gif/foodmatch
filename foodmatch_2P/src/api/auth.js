import client from './client';

// Backend: CustomerAuthController::registration — expects f_name, l_name,
// email, phone, password. Returns {token} directly, or {temporary_token}
// if email/phone verification is turned on in the admin panel.
export const registerCustomer = ({ firstName, lastName, email, phone, password }) =>
  client
    .post('/auth/registration', {
      f_name: firstName,
      l_name: lastName,
      email,
      phone,
      password,
    })
    .then((res) => res.data);

// Backend: CustomerAuthController::login — expects email_or_phone, password,
// type ('email'|'phone'). Returns {token, status:true} or {temporary_token}.
export const loginCustomer = ({ emailOrPhone, password }) =>
  client
    .post('/auth/login', {
      email_or_phone: emailOrPhone,
      password,
      type: 'email',
    })
    .then((res) => res.data);
