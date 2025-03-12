import React from 'react';
import ErpLogo from '../img/onboarding_svg';

const Header = () => {
  return (
    <header className="erp-onboarding-header">
      <div className="header-left">
        <span className="logo"><ErpLogo /></span>
      </div>
      
      <div className="header-right">
        <a href="#" className="skip-link">Skip Setup</a>
      </div>
    </header>
  );
};

export default Header;