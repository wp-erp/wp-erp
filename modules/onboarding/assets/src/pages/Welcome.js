import React from 'react';

/**
 * Welcome Page Component
 * 
 * The first page in the onboarding process that introduces the user to the setup wizard
 */
const Welcome = ({ onComplete }) => {
  return (
    <div className="erp-onboarding-page welcome-page">
      <div className="page-header">
        <h1>Welcome to ERP System</h1>
        <p className="subtitle">Let's set up your business with just a few steps</p>
      </div>
      
      <div className="page-content">
        <div className="welcome-card">
          <img 
            src="/wp-content/plugins/wp-erp/modules/onboarding/assets/images/welcome.svg" 
            alt="Welcome" 
            className="welcome-image"
          />
          
          <div className="welcome-text">
            <h2>Quick Setup Wizard</h2>
            <p>
              This wizard will help you configure your ERP system with the essential 
              information needed to get started. The process takes just a few minutes 
              to complete.
            </p>
            
            <h3>Here's what you'll do:</h3>
            <ul>
              <li>Enter your company information</li>
              <li>Select which modules you want to enable</li>
              <li>Set up user accounts and permissions</li>
              <li>Get ready to use your new ERP system</li>
            </ul>
          </div>
        </div>
      </div>
      
      <div className="page-actions">
        <button 
          className="button button-primary button-large"
          onClick={onComplete}
        >
          Let's Get Started
        </button>
      </div>
    </div>
  );
};

export default Welcome;