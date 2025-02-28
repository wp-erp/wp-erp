import React, { useState } from 'react';
import { Routes, Route, Navigate } from 'react-router';
import Header from './Header';
import Sidebar from './Sidebar';
import ProgressBar from './ProgressBar';

// Onboarding Steps
import Welcome from '../pages/Welcome';
import CompanyInfo from '../pages/CompanyInfo';
import ModuleSelection from '../pages/ModuleSelection';
import UserSetup from '../pages/UserSetup';
import Completion from '../pages/Completion';

/**
 * Main App Component
 * 
 * This component acts as the container for the entire onboarding process
 */
const App = () => {
  // Define the steps for onboarding
  const steps = [
    { id: 'welcome', title: 'Welcome', path: '/welcome', component: Welcome },
    { id: 'company', title: 'Company Information', path: '/company', component: CompanyInfo },
    { id: 'modules', title: 'Module Selection', path: '/modules', component: ModuleSelection },
    { id: 'users', title: 'User Setup', path: '/users', component: UserSetup },
    { id: 'complete', title: 'Complete', path: '/complete', component: Completion },
  ];
  
  // State to track current step and progress
  const [currentStep, setCurrentStep] = useState(0);
  const [stepsCompleted, setStepsCompleted] = useState({});
  
  // Calculate progress percentage
  const completedCount = Object.values(stepsCompleted).filter(Boolean).length;
  const progress = Math.round((completedCount / (steps.length - 1)) * 100);
  
  // Mark a step as completed and move to the next step
  const completeStep = (stepId) => {
    const newStepsCompleted = { ...stepsCompleted, [stepId]: true };
    setStepsCompleted(newStepsCompleted);
    
    // Find the next step index
    const currentIndex = steps.findIndex(step => step.id === stepId);
    if (currentIndex < steps.length - 1) {
      setCurrentStep(currentIndex + 1);
    }
  };
  
  // Navigate to a specific step (if it's accessible)
  const goToStep = (index) => {
    // Only allow navigating to completed steps or the next step
    if (index <= currentStep || stepsCompleted[steps[index - 1]?.id]) {
      setCurrentStep(index);
    }
  };
  
  return (
    <div className="erp-onboarding-container">
      <Header />
      
      <ProgressBar percentage={progress} />
      
      <div className="erp-onboarding-content">
        <Sidebar 
          steps={steps} 
          currentStep={currentStep}
          completedSteps={stepsCompleted}
          onStepClick={goToStep}
        />
        
        <main className="erp-onboarding-main">
          <Routes>
            {steps.map((step) => (
              <Route 
                key={step.id}
                path={step.path} 
                element={
                  <step.component 
                    onComplete={() => completeStep(step.id)}
                  />
                } 
              />
            ))}
            
            {/* Default redirect to the first step */}
            <Route path="*" element={<Navigate to={steps[0].path} replace />} />
          </Routes>
        </main>
      </div>
      
      <footer className="erp-onboarding-footer">
        <p>ERP Onboarding - Step {currentStep + 1} of {steps.length}</p>
      </footer>
    </div>
  );
};

export default App;