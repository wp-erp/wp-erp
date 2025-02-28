import React from 'react';

/**
 * Header Component
 * 
 * Displays the top header of the onboarding process with logo and title
 */
const Header = () => {
  return (
    <header className="erp-onboarding-header">
      <div className="header-left">
        <img 
          src="/wp-content/plugins/wp-erp/assets/images/erp-logo.svg" 
          alt="ERP Logo" 
          className="erp-onboarding-logo" 
        />
        <h1>ERP System Setup</h1>
      </div>
      
      <div className="header-right">
        <a href="#" className="header-link">Skip Setup</a>
      </div>
    </header>
  );
};

export default Header;