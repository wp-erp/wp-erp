import React from 'react';
import ReactDOM from 'react-dom';
import './css/style.css';
import AppComponenet from './AppComponenet';
import ImportEmployee from './pages/ImportEmployee';

// Simple App Component - No router dependencies


// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('erp-onboarding-app');
  // const import_component = document.getElementById('csv_import_react');

  if (container) {
    ReactDOM.render(<AppComponenet />, container);
  }
});
