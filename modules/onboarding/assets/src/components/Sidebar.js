import React from 'react';

/**
 * Sidebar Component
 * 
 * Displays the left sidebar with the steps of the onboarding process
 */
const Sidebar = ({ steps, currentStep, completedSteps, onStepClick }) => {
  return (
    <div className="erp-onboarding-sidebar">
      <div className="erp-steps-container">
        {steps.map((step, index) => {
          // Determine step state classes
          const isActive = index === currentStep;
          const isCompleted = completedSteps[step.id];
          const isDisabled = index > currentStep && !isCompleted;
          
          const stepClass = `erp-step ${isActive ? 'active' : ''} ${isCompleted ? 'completed' : ''} ${isDisabled ? 'disabled' : ''}`;
          
          return (
            <div 
              key={step.id} 
              className={stepClass}
              onClick={() => !isDisabled && onStepClick(index)}
            >
              <div className="erp-step-number">
                {isCompleted ? (
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                ) : (
                  index + 1
                )}
              </div>
              <div className="erp-step-title">{step.title}</div>
            </div>
          );
        })}
      </div>
      
      <div className="erp-sidebar-footer">
        <p>Need help? <a href="#" className="support-link">Contact Support</a></p>
      </div>
    </div>
  );
};

export default Sidebar;