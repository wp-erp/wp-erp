const CompleteStep = ({ onComplete }) => {
  const handleFinish = () => {
    onComplete();
  };

  return (
    <div>
      {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
      <div className="max-w-640px mx-auto overflow-visible">
        {/* Congratulations Card - matches erp-congratulations-card from setup.css */}
        <div className="bg-white rounded-2xl p-24 text-center my-8 max-w-640px max-h-340px border border-gray-300">
          {/* Confetti Icon - with celebrate animation */}
          <div className="mb-6" style={{ animation: 'celebrate 0.5s ease' }}>
            🎉
          </div>

          {/* Heading - matches setup.css h2 styling */}
          <h2 className="text-xl font-medium text-black m-0 mb-1">
            Congratulations!
          </h2>

          {/* Description - matches setup.css p styling */}
          <p className="text-slate-500 text-sm leading-5 m-0 font-normal">
            Your WP ERP is now ready to use. Let's get started!
          </p>
        </div>

        {/* Go to Dashboard Button - matches erp-go-to-dashboard-container */}
        <div className="text-center mt-8">
          <button
            onClick={handleFinish}
            className="btn-primary no-underline"
          >
            Go to Dashboard
          </button>
        </div>
      </div>
    </div>
  );
};

export default CompleteStep;
