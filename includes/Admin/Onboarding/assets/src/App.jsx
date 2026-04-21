import { useState, useEffect } from 'react';
import Layout from './components/Layout';
import Progress from './components/Progress';
import BasicStep from './components/steps/BasicStep';
import OrganizationStep from './components/steps/OrganizationStep';
import LeaveStep from './components/steps/LeaveStep';
import WorkdayStep from './components/steps/WorkdayStep';
import ImportStep from './components/steps/ImportStep';
import CompleteStep from './components/steps/CompleteStep';
import { completeOnboarding, getOnboardingStatus, saveStepData } from './utils/api';

const STEPS = [
  { id: 'basic', name: '1. Basic Settings', component: BasicStep },
  { id: 'organization', name: '2. Department and Designation', component: OrganizationStep },
  { id: 'leave', name: '3. Leave Setup', component: LeaveStep },
  { id: 'workday', name: '4. Workday Setup', component: WorkdayStep },
  { id: 'employee', name: '5. Import Employee', component: ImportStep },
  { id: 'complete', name: '6. Complete', component: CompleteStep },
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
            : (() => {
                const year = new Date().getFullYear();
                return [{
                  id: Date.now(),
                  fy_name: String(year),
                  start_date: `${year}-01-01`,
                  end_date: `${year}-12-31`
                }];
              })(),
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

  const handleNext = async (stepData) => {
    // Merge step data with existing form data
    const updatedData = { ...formData, ...stepData };
    setFormData(updatedData);
    setError(null);

    // Save data for current step (except import step which is handled separately)
    const currentStepId = STEPS[currentStep].id;
    if (currentStepId !== 'import' && currentStepId !== 'complete') {
      setLoading(true);
      try {
        await saveStepData(updatedData);
      } catch (err) {
        console.error('Error saving step data:', err);
        setError(err.response?.data?.message || 'Failed to save data. Please try again.');
        setLoading(false);
        return; // Don't proceed to next step if save fails
      }
      setLoading(false);
    }

    // Move to next step
    setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
  };

  const handleBack = () => {
    setCurrentStep((prev) => Math.max(prev - 1, 0));
  };

  const handleSkip = () => {
    // Skip current step and move to next step
    setCurrentStep((prev) => Math.min(prev + 1, STEPS.length - 1));
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

  if (loading && Object.keys(formData).length === 0) {
    return (
      <div className="fixed inset-0 flex items-center justify-center bg-white z-50">
        <div className="flex flex-col items-center gap-4">
          <div className="w-10 h-10 border-3 border-blue-100 border-t-blue-500 rounded-full animate-spin" style={{ borderWidth: '3px' }}></div>
          <p className="text-gray-500 text-sm font-medium">Loading...</p>
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
        <div className="fixed inset-0 flex items-center justify-center z-50" style={{ backdropFilter: 'blur(2px)', backgroundColor: 'rgba(0,0,0,0.15)' }}>
          <div className="bg-white rounded-xl px-8 py-6 shadow-xl flex flex-col items-center gap-3" style={{ minWidth: '160px' }}>
            <div className="w-9 h-9 rounded-full animate-spin" style={{ borderWidth: '3px', borderStyle: 'solid', borderColor: '#dbeafe', borderTopColor: '#3b82f6' }}></div>
            <p className="text-gray-600 text-sm font-medium m-0">Saving...</p>
          </div>
        </div>
      )}
    </Layout>
  );
}

export default App;
