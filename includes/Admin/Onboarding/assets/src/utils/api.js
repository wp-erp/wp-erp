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
export const saveBasicSettings = (data) => {
  return apiClient.post('/onboarding/basic', data);
};

export const saveOrganization = (data) => {
  return apiClient.post('/onboarding/organization', data);
};

export const importEmployees = (data) => {
  return apiClient.post('/onboarding/import-employees', data);
};

export const saveModuleSettings = (data) => {
  return apiClient.post('/onboarding/modules', data);
};

export const completeOnboarding = (data) => {
  return apiClient.post('/onboarding/complete', data);
};

export const getOnboardingStatus = () => {
  return apiClient.get('/onboarding/status');
};

export default apiClient;
