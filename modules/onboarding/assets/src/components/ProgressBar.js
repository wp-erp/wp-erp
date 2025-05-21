import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

const ProgressBar = () => {
  const location = useLocation();

  const steps = [
    { path: '/company-details', label: 'Company Details' },
    { path: '/department-designation', label: 'Department & Designation' },
    // { path: '/import-employee', label: 'Import Employee' },
    { path: '/leave', label: 'Leave Management' },
    { path: '/workdays', label: 'Workday Setup' },
  ];

  const navigate = useNavigate();
  const currentStepIndex = steps.findIndex(step => location.pathname === step.path);
  const totalSteps = steps.length;
  const percentage = ((currentStepIndex + 1) / totalSteps) * 100;

  const handleStepClick = (index) => {
    if (index <= currentStepIndex + 1) {
      navigate(steps[index].path);
    }
  };

  return (
    <div className="erp-onboarding-progress-container">
      <div className="erp-onboarding-steps">
        {steps.map((step, index) => (
          <div
            key={step.path}
            className={`step-item ${index === currentStepIndex ? 'active' : ''} ${index < currentStepIndex ? 'completed' : ''}`}
            onClick={() => handleStepClick(index)}
          >
            <div className="step-number">{index + 1}</div>
            <div className="step-label">{step.label}</div>
          </div>
        ))}
      </div>
      <div className="erp-onboarding-progress">
        <div
          className="erp-onboarding-progress-bar"
          style={{ width: `${percentage}%` }}
          role="progressbar"
          aria-valuenow={percentage}
          aria-valuemin="0"
          aria-valuemax="100"
        ></div>
      </div>
    </div>
  );
};

export default ProgressBar;
