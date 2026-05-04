import { useState } from 'react';

const CompleteStep = ({ onComplete }) => {
  const [isRedirecting, setIsRedirecting] = useState(false);

  const distUrl = window.wpErpOnboarding?.distUrl || '';
  const congratulationImageUrl = distUrl ? distUrl + '/images/congratulation.png' : '';

  const handleFinish = async () => {
    setIsRedirecting(true);
    await onComplete();
    // onComplete redirects; if it throws, reset
    setIsRedirecting(false);
  };

  return (
    <div>
      <div className="max-w-640px mx-auto overflow-visible">
        <div className="bg-white rounded-2xl p-24 text-center my-8 max-w-640px max-h-340px border border-gray-300">
          <div className="mb-6 flex justify-center" style={{ animation: 'celebrate 0.5s ease' }}>
            {congratulationImageUrl ? (
              <img src={congratulationImageUrl} alt="Congratulations" className="w-16 h-16" />
            ) : (
              <span style={{ fontSize: '64px' }}>🎉</span>
            )}
          </div>

          <h2 className="text-xl font-medium text-black m-0 mb-1">
            You're All Set!
          </h2>

          <p className="text-slate-500 text-sm leading-5 m-0 font-normal">
            Your WP ERP is configured and ready to go. Start managing your business now!
          </p>
        </div>

        <div className="text-center mt-8">
          <button
            onClick={handleFinish}
            className="btn-primary no-underline"
            disabled={isRedirecting}
          >
            {isRedirecting && <span className="btn-spinner"></span>}
            {isRedirecting ? 'Redirecting...' : 'Go to Dashboard'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default CompleteStep;
