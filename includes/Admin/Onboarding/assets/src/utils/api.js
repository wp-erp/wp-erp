import axios from 'axios';

// Get WordPress REST API nonce and endpoint from global object
const getApiConfig = () => {
  if (typeof window.wpErpOnboarding !== 'undefined') {
    return {
      nonce: window.wpErpOnboarding.nonce,
      apiUrl: window.wpErpOnboarding.apiUrl,
      adminUrl: window.wpErpOnboarding.adminUrl,
    };
  }
  return {
    nonce: '',
    apiUrl: '/wp-json/erp/v1',
    adminUrl: '/wp-admin',
  };
};

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: getApiConfig().apiUrl,
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': getApiConfig().nonce,
  },
});

// API methods
export const getOnboardingStatus = () => {
  return apiClient.get('/onboarding/status');
};

export const saveStepData = (data) => {
  return apiClient.post('/onboarding/save-step', data);
};

export const completeOnboarding = (data) => {
  return apiClient.post('/onboarding/complete', data);
};

export default apiClient;
