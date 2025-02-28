import React from 'react';
import ReactDOM from 'react-dom';
import './css/style.css';
import AppComponenet from './AppComponenet';

// Simple App Component - No router dependencies


// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('erp-onboarding-app');
  
  if (container) {
    ReactDOM.render(<AppComponenet />, container);
  }
});