import React, { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const steps = [
    {
        key: 'introduction',
        name: __('Introduction', 'erp'),
        component: null
    },
    {
        key: 'basic',
        name: __('Basic', 'erp'),
        component: null
    },
    {
        key: 'module',
        name: __('Module', 'erp'),
        component: null
    },
    {
        key: 'email',
        name: __('E-Marketing', 'erp'),
        component: null
    },
    {
        key: 'department',
        name: __('Departments', 'erp'),
        component: null
    },
    {
        key: 'designation',
        name: __('Designations', 'erp'),
        component: null
    },
    {
        key: 'workdays',
        name: __('Work Days', 'erp'),
        component: null
    },
    {
        key: 'next_steps',
        name: __('Ready!', 'erp'),
        component: null
    }
];

const SetupWizard = () => {
    const [currentStep, setCurrentStep] = useState(0);

    const handleNext = () => {
        if (currentStep < steps.length - 1) {
            setCurrentStep(currentStep + 1);
        }
    };

    const handlePrevious = () => {
        if (currentStep > 0) {
            setCurrentStep(currentStep - 1);
        }
    };

    const handleSkip = () => {
        handleNext();
    };

    return (
        <div className="erp-setup-wizard">
            <div className="erp-setup-wizard-steps">
                <ol className="erp-setup-steps">
                    {steps.map((step, index) => (
                        <li
                            key={step.key}
                            className={`
                                ${index === currentStep ? 'active' : ''}
                                ${index < currentStep ? 'done' : ''}
                            `}
                        >
                            <a href={`#${step.key}`}>{step.name}</a>
                        </li>
                    ))}
                </ol>
            </div>

            <div className="erp-setup-content">
                {/* Step content will be rendered here */}
                <div className="step-content">
                    {steps[currentStep].component}
                </div>

                <div className="erp-setup-actions step">
                    {currentStep > 0 && (
                        <button
                            className="button button-large"
                            onClick={handlePrevious}
                        >
                            {__('Previous', 'erp')}
                        </button>
                    )}

                    <button
                        className="button-primary button button-large button-next"
                        onClick={handleNext}
                    >
                        {currentStep === steps.length - 1
                            ? __('Finish', 'erp')
                            : __('Continue', 'erp')}
                    </button>

                    {currentStep < steps.length - 1 && (
                        <button
                            className="button button-large button-next"
                            onClick={handleSkip}
                        >
                            {__('Skip this step', 'erp')}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default SetupWizard;