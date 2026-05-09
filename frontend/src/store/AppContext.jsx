import { createContext, useCallback, useContext, useEffect, useMemo, useReducer } from 'react';
import { authService } from '../services/authService';

const AppContext = createContext(null);

const initialState = {
  token: localStorage.getItem('bizpilot_token'),
  user: JSON.parse(localStorage.getItem('bizpilot_user') || 'null'),
  tenant: JSON.parse(localStorage.getItem('bizpilot_tenant') || 'null'),
  theme: localStorage.getItem('bizpilot_theme') || 'light',
};

function appReducer(state, action) {
  switch (action.type) {
    case 'AUTH_SUCCESS':
      return {
        ...state,
        token: action.payload.token,
        user: action.payload.user,
      };
    case 'SET_TENANT':
      return {
        ...state,
        tenant: action.payload,
      };
    case 'LOGOUT':
      return {
        ...state,
        token: null,
        user: null,
        tenant: null,
      };
    case 'SET_THEME':
      return {
        ...state,
        theme: action.payload,
      };
    default:
      return state;
  }
}

export function AppProvider({ children }) {
  const [state, dispatch] = useReducer(appReducer, initialState);

  useEffect(() => {
    document.documentElement.classList.toggle('dark', state.theme === 'dark');
    localStorage.setItem('bizpilot_theme', state.theme);
  }, [state.theme]);

  const persistAuth = useCallback((payload) => {
    localStorage.setItem('bizpilot_token', payload.token);
    localStorage.setItem('bizpilot_user', JSON.stringify(payload.user));
    dispatch({ type: 'AUTH_SUCCESS', payload });
  }, []);

  const login = useCallback(async (payload) => {
    const result = await authService.login(payload);
    persistAuth(result);
    return result;
  }, [persistAuth]);

  const register = useCallback(async (payload) => {
    const result = await authService.register(payload);
    persistAuth(result);
    return result;
  }, [persistAuth]);

  const logout = useCallback(async () => {
    try {
      if (state.token) {
        await authService.logout();
      }
    } finally {
      localStorage.removeItem('bizpilot_token');
      localStorage.removeItem('bizpilot_user');
      localStorage.removeItem('bizpilot_tenant');
      dispatch({ type: 'LOGOUT' });
    }
  }, [state.token]);

  const setTenant = useCallback((tenant) => {
    if (tenant) {
      localStorage.setItem('bizpilot_tenant', JSON.stringify(tenant));
    } else {
      localStorage.removeItem('bizpilot_tenant');
    }

    dispatch({ type: 'SET_TENANT', payload: tenant });
  }, []);

  const toggleTheme = useCallback(() => {
    dispatch({ type: 'SET_THEME', payload: state.theme === 'dark' ? 'light' : 'dark' });
  }, [state.theme]);

  const value = useMemo(() => ({
    ...state,
    isAuthenticated: Boolean(state.token),
    login,
    register,
    logout,
    setTenant,
    toggleTheme,
  }), [state, login, register, logout, setTenant, toggleTheme]);

  return (
    <AppContext.Provider value={value}>
      {children}
    </AppContext.Provider>
  );
}

export function useApp() {
  const context = useContext(AppContext);

  if (!context) {
    throw new Error('useApp must be used inside AppProvider');
  }

  return context;
}
