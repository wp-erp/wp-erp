import { useState, useEffect } from 'react';
import Layout from './components/Layout';
import Progress from './components/Progress';
import BasicStep from './components/steps/BasicStep';
import OrganizationStep from './components/steps/OrganizationStep';
import ImportStep from './components/steps/ImportStep';
import ModuleStep from './components/steps/ModuleStep';
import CompleteStep from './components/steps/CompleteStep';
import { completeOnboarding, getOnboardingStatus } from './utils/api';

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
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Load initial data from database on mount
  useEffect(() => {
    const loadInitialData = async () => {
      try {
        const response = await getOnboardingStatus();
        const data = response.data;

        // If onboarding is already completed, redirect to dashboard
        if (data.completed) {
          if (window.wpErpOnboarding?.adminUrl) {
            window.location.href = window.wpErpOnboarding.adminUrl;
          }
          return;
        }

        // Pre-fill form with existing data from database
        setFormData({
          companyName: data.companyName || '',
          companyStartDate: data.companyStartDate || '',
          financialYearStarts: data.financialYearStarts || 'january',
          departments: data.departments || [],
          designations: data.designations || [],
          leaveYears: data.leaveYears && data.leaveYears.length > 0
            ? data.leaveYears.map(fy => ({
                id: Date.now() + Math.random(),
                fy_name: fy.fy_name,
                start_date: fy.start_date,
                end_date: fy.end_date
              }))
            : [{
                id: Date.now(),
                fy_name: '',
                start_date: '',
                end_date: ''
              }],
          enableLeaveManagement: data.enableLeaveManagement ?? true,
          workingDays: data.workingDays || {
            mon: '8',
            tue: '8',
            wed: '8',
            thu: '8',
            fri: '8',
            sat: '0',
            sun: '0'
          },
          workingHours: data.workingHours || {
            start: '09:00',
            end: '17:00'
          }
        });

        setLoading(false);
      } catch (err) {
        console.error('Error loading initial data:', err);
        setLoading(false);
        // Continue anyway with empty form
      }
    };

    loadInitialData();
  }, []);

  const handleNext = (stepData) => {
    // Collect data without saving - save everything at the end
    const updatedData = { ...formData, ...stepData };
    setFormData(updatedData);
    setError(null);
    
    // Move to next step
    setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
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
    setError(null);
    
    try {
      // Save ALL collected data in one atomic transaction
      await completeOnboarding(formData);
      
      // Redirect to dashboard on success
      if (window.wpErpOnboarding?.adminUrl) {
        window.location.href = window.wpErpOnboarding.adminUrl;
      }
    } catch (err) {
      console.error('Error completing onboarding:', err);
      setError(err.response?.data?.message || 'Failed to complete setup. Please try again.');
      setLoading(false);
    }
  };

  const CurrentStepComponent = STEPS[currentStep].component;
  const isLastStep = currentStep === STEPS.length - 1;
  const isFirstStep = currentStep === 0;

  // Show loading spinner while fetching initial data
  if (loading && Object.keys(formData).length === 0) {
    return (
      <div className="fixed inset-0 flex items-center justify-center bg-white z-50">
        <div className="bg-white rounded-lg p-6">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
          <p className="text-gray-700 mt-4 text-center">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <Layout
      onSkip={!isLastStep ? handleSkip : null}
      onBack={!isFirstStep && !isLastStep ? handleBack : null}
    >
      <Progress
        currentStep={currentStep + 1}
        totalSteps={STEPS.length}
        stepName={STEPS[currentStep].name}
        error={error}
      />

      <div className={loading ? 'opacity-50 pointer-events-none' : ''}>
        <CurrentStepComponent
          onNext={handleNext}
          onComplete={handleComplete}
          initialData={formData}
        />
      </div>

      {loading && Object.keys(formData).length > 0 && (
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
