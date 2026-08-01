import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { tokenStorage, setUnauthorizedHandler, extractErrorMessage } from '../../api/client';
import { loginCustomer, registerCustomer } from '../../api/auth';
import { getCustomerInfo } from '../../api/customer';

const AuthContext = createContext(null);

function mapUser(info) {
  return {
    id: info.id,
    name: `${info.f_name || ''} ${info.l_name || ''}`.trim() || 'Usuario',
    email: info.email,
    phone: info.phone,
  };
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [isGuest, setIsGuest] = useState(false);
  // true while we check for a stored token on app boot, so SplashScreen can
  // wait instead of flashing the login screen for logged-in users.
  const [isBooting, setIsBooting] = useState(true);

  const loadProfile = async () => {
    const info = await getCustomerInfo();
    setUser(mapUser(info));
    setIsGuest(false);
  };

  useEffect(() => {
    setUnauthorizedHandler(() => {
      tokenStorage.clear();
      setUser(null);
    });

    (async () => {
      const token = await tokenStorage.get();
      if (token) {
        try {
          await loadProfile();
        } catch {
          await tokenStorage.clear();
        }
      }
      setIsBooting(false);
    })();
  }, []);

  const login = async (emailOrPhone, password) => {
    const data = await loginCustomer({ emailOrPhone, password });
    if (!data.token) {
      // email/phone verification required by admin settings — no OTP flow
      // built yet, surface it clearly instead of pretending it worked.
      throw new Error('Tu cuenta requiere verificación antes de iniciar sesión.');
    }
    await tokenStorage.set(data.token);
    await loadProfile();
  };

  const register = async ({ firstName, lastName, email, phone, password }) => {
    const data = await registerCustomer({ firstName, lastName, email, phone, password });
    if (!data.token) {
      throw new Error('Cuenta creada. Verifica tu correo o teléfono antes de iniciar sesión.');
    }
    await tokenStorage.set(data.token);
    await loadProfile();
  };

  const continueAsGuest = () => {
    setIsGuest(true);
    setUser(null);
  };

  const logout = async () => {
    await tokenStorage.clear();
    setUser(null);
    setIsGuest(false);
  };

  const value = useMemo(
    () => ({
      user,
      isGuest,
      isBooting,
      isAuthenticated: !!user,
      login,
      register,
      continueAsGuest,
      logout,
      refreshProfile: loadProfile,
    }),
    [user, isGuest, isBooting]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = () => useContext(AuthContext);
export { extractErrorMessage };
export default AuthContext;
