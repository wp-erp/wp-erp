import React from 'react';
import ErpLogo from '../img/onboarding_svg';
import { Link } from 'react-router-dom';
const Header = () => {

  return (
    <header className="erp-onboarding-header">
      <div className="header-left">
        <span className="logo"><ErpLogo /></span>
      </div>
      
      <div className="header-right">
        <a href={window.location.origin + window.location.pathname + "?page=erp" } className="skip-link">Skip Setup</a>
        {/* <Link to="wp-admin/admin.php?page=erp" className="skip-link">Skip Setup</Link> */}
      </div>
    </header>
  );
};

export default Header;