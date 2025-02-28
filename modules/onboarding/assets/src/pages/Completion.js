import React from 'react';

/**
 * Completion Page Component
 * 
 * The final step in the onboarding process that confirms setup is complete
 */
const Completion = ({ onComplete }) => {
  // Handle finishing the onboarding process
  const handleFinish = () => {
    // In a real implementation, you might redirect to the dashboard
    window.location.href = '/wp-admin/admin.php?page=erp-dashboard';
  };
  
  return (
    <div className="erp-onboarding-page completion-page">
      <div className="page-header">
        <h1>Setup Complete!</h1>
        <p className="subtitle">Your ERP system is now ready to use</p>
      </div>
      
      <div className="page-content">
        <div className="completion-container">
          <div className="completion-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="10" stroke="#4CAF50" strokeWidth="2" fill="none" />
              <path d="M8 12L11 15L16 9" stroke="#4CAF50" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </div>
          
          <div className="completion-message">
            <h2>Congratulations!</h2>
            <p>
              You have successfully set up your ERP system. Your system is now configured and ready for you to use.
              Here's what has been set up based on your selections:
            </p>
            
            <ul className="setup-summary">
              <li>
                <strong>Company Profile</strong>
                <span>Your business information has been configured</span>
              </li>
              <li>
                <strong>Modules Enabled</strong>
                <span>Accounting, HR, and CRM modules are activated</span>
              </li>
              <li>
                <strong>User Accounts</strong>
                <span>2 users created with appropriate permissions</span>
              </li>
            </ul>
            
            <h3>What's Next?</h3>
            <p>
              You can now proceed to your dashboard and start using your ERP system.
              We recommend the following next steps:
            </p>
            
            <ul className="next-steps">
              <li>Review your company settings and make any additional adjustments</li>
              <li>Import your existing data into the system</li>
              <li>Explore the features available in your activated modules</li>
              <li>Set up additional customizations specific to your business needs</li>
            </ul>
          </div>
        </div>
        
        <div className="resources-container">
          <h3>Helpful Resources</h3>
          <div className="resources-grid">
            <a href="#" className="resource-card">
              <div className="resource-icon">
                <span className="dashicons dashicons-media-document"></span>
              </div>
              <div className="resource-title">Documentation</div>
              <div className="resource-description">Access our comprehensive documentation to learn more about your ERP system</div>
            </a>

            <a href="#" className="resource-card">
              <div className="resource-icon">
                <span className="dashicons dashicons-video-alt3"></span>
              </div>
              <div className="resource-title">Video Tutorials</div>
              <div className="resource-description">Watch video tutorials to get started with your ERP system</div>
            </a>
            <a href="#" className="resource-card">
              <div className="resource-icon">
                <span className="dashicons dashicons-sos"></span>
              </div>
              <div className="resource-title">Support</div>
              <div className="resource-description">Get in touch with our support team if you need assistance</div>
            </a>
          </div>
        </div>
      </div>

      <div className="page-actions">
        <button
          className="button button-primary button-large"
          onClick={handleFinish}
        >
          Finish
        </button>
      </div>
    </div>
  );
};
export default Completion;