import React, { useState } from 'react';
import { Routes, Route, Navigate, useLocation } from 'react-router';
import Header from './Header';
// import Sidebar from './Sidebar';
import ProgressBar from './ProgressBar';

// Onboarding Steps
// import ModuleSelection from '../pages/ModuleSelection';
// import UserSetup from '../pages/UserSetup';
// import Completion from '../pages/Completion';
import CompanyDetails from '../pages/CompanyDetails';
import DepartmentDesignation from '../pages/DepartmentDesignation';
import ImportEmployee from '../pages/ImportEmployee';



const App = () => {
  return (
    <div className="erp-onboarding-wizard">
      <Header />
      <div className="wizard-content">
        <ProgressBar />
        <Routes>
          <Route path="/" element={<Navigate to="/company-details" />} />
          <Route path="/company-details" element={<CompanyDetails />} />
          <Route path="/department-designation" element={<DepartmentDesignation />} />
          <Route path="/import-employee" element={<ImportEmployee />} />
        </Routes>
      </div>
    </div>
  );
};

export default App;