const Progress = ({ currentStep, totalSteps, stepName, error }) => {
  return (
    // Matches erp-progress-container from setup.css
    <div className="w-640px mx-auto mb-16 flex flex-col gap-6">
      {/* Progress Wrapper - matches erp-progress-wrapper */}
      <div className="flex items-center gap-4">
        {/* Progress Bars - matches erp-progress-bars */}
        <div className="flex gap-5 flex-1">
          {Array.from({ length: totalSteps }).map((_, index) => (
            <div
              key={index}
              className={`progress-bar flex-1 ${
                index < currentStep ? 'progress-bar-active' : ''
              }`}
            />
          ))}
        </div>
        {/* Progress Counter - matches erp-progress-counter */}
        <div className="text-gray-700 text-base leading-6 font-medium whitespace-nowrap flex-shrink-0">
          {currentStep}/{totalSteps}
        </div>
      </div>
      {/* Step Label - matches erp-step-label */}
      {stepName && (
        <div className="text-center text-slate-500 text-sm leading-[14px] font-normal">
          {stepName}
        </div>
      )}
      {/* Error Message - shown below step label (sub-header) */}
      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mt-2">
          <p className="text-red-800 text-sm m-0">{error}</p>
        </div>
      )}
    </div>
  );
};

export default Progress;
