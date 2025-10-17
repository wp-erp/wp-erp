import { useState } from 'react';
import Layout from './components/Layout';
import Progress from './components/Progress';
import BasicStep from './components/steps/BasicStep';
import OrganizationStep from './components/steps/OrganizationStep';
import ImportStep from './components/steps/ImportStep';
import ModuleStep from './components/steps/ModuleStep';
import CompleteStep from './components/steps/CompleteStep';
import {
  saveBasicSettings,
  saveOrganization,
  importEmployees,
  saveModuleSettings,
  completeOnboarding,
} from './utils/api';

const STEPS = [
  { id: 'basic', name: '1. Basic Setting', component: BasicStep },
  { id: 'organization', name: '2. Department and Designation', component: OrganizationStep },
  { id: 'import', name: '3. Import Employees', component: ImportStep },
  { id: 'module', name: '4. Leave and Workday Setup', component: ModuleStep },
  { id: 'complete', name: '5. Complete', component: CompleteStep },
];

function App() {
  const [currentStep, setCurrentStep] = useState(0);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleNext = async (stepData) => {
    setLoading(true);
    setError(null);

    try {
      const updatedData = { ...formData, ...stepData };
      setFormData(updatedData);

      // Save to backend based on current step
      const stepId = STEPS[currentStep].id;

      switch (stepId) {
        case 'basic':
          await saveBasicSettings(stepData);
          break;
        case 'organization':
          await saveOrganization(stepData);
          break;
        case 'import':
          if (stepData.file) {
            const formDataObj = new FormData();
            formDataObj.append('file', stepData.file);
            await importEmployees(formDataObj);
          }
          break;
        case 'module':
          await saveModuleSettings(stepData);
          break;
        default:
          break;
      }

      setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
    } catch (err) {
      console.error('Error saving step data:', err);
      setError(err.response?.data?.message || 'An error occurred. Please try again.');
      // Still allow moving forward even if API fails (for development)
      if (process.env.NODE_ENV === 'development') {
        setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
      }
    } finally {
      setLoading(false);
    }
  };

  const handleBack = () => {
    setCurrentStep((prev) => Math.max(prev - 1, 0));
  };

  const handleSkip = () => {
    if (window.wpErpOnboarding?.adminUrl) {
      window.location.href = window.wpErpOnboarding.adminUrl;
    }
  };

  const handleComplete = async () => {
    setLoading(true);
    try {
      await completeOnboarding();
      if (window.wpErpOnboarding?.adminUrl) {
        window.location.href = window.wpErpOnboarding.adminUrl;
      }
    } catch (err) {
      console.error('Error completing onboarding:', err);
      // Redirect anyway
      if (window.wpErpOnboarding?.adminUrl) {
        window.location.href = window.wpErpOnboarding.adminUrl;
      }
    } finally {
      setLoading(false);
    }
  };

  const CurrentStepComponent = STEPS[currentStep].component;
  const isLastStep = currentStep === STEPS.length - 1;

  return (
    <Layout onSkip={!isLastStep ? handleSkip : null}>
      <Progress
        currentStep={currentStep + 1}
        totalSteps={STEPS.length}
        stepName={STEPS[currentStep].name}
      />

      {error && (
        <div className="max-w-2xl mx-auto mb-4">
          <div className="bg-red-50 border border-red-200 rounded-lg p-4">
            <p className="text-red-800 text-sm m-0">{error}</p>
          </div>
        </div>
      )}

      <div className={loading ? 'opacity-50 pointer-events-none' : ''}>
        <CurrentStepComponent
          onNext={handleNext}
          onBack={handleBack}
          onComplete={handleComplete}
          initialData={formData}
        />
      </div>

      {loading && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-20 z-50">
          <div className="bg-white rounded-lg p-6 shadow-lg">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
            <p className="text-gray-700 mt-4 text-center">Saving...</p>
          </div>
        </div>
      )}
    </Layout>
  );
}

export default App;
