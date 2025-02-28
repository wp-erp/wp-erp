import React from 'react';

/**
 * ProgressBar Component
 * 
 * Displays a horizontal progress bar showing completion status
 */
const ProgressBar = ({ percentage }) => {
  // Ensure percentage is within 0-100 range
  const validPercentage = Math.min(100, Math.max(0, percentage || 0));
  
  return (
    <div className="erp-onboarding-progress">
      <div 
        className="erp-onboarding-progress-bar" 
        style={{ width: `${validPercentage}%` }}
        role="progressbar"
        aria-valuenow={validPercentage}
        aria-valuemin="0"
        aria-valuemax="100"
      ></div>
    </div>
  );
};

export default ProgressBar;