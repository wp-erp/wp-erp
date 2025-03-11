import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const CompanyDetails = ({ onComplete }) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    company_name: '',
    company_start: '',
    financial_year: 'January'
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  useEffect(() => {
    // Fetch existing company settings if any
    const fetchCompanySettings = async () => {
      try {
        const response = await axios.get('/wp-json/erp/v1/onboarding/company');
        setFormData(response.data);
      } catch (err) {
        console.error('Error fetching company settings:', err);
      }
    };

    fetchCompanySettings();
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
    //   await axios.put('/wp-json/erp/v1/onboarding/company', formData);
    //   onComplete && onComplete();
      navigate('/department-designation'); // Add leading slash for hash router
    } catch (err) {
      setError('Failed to save company details. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="erp-onboarding-page company-details">
      <div className="page-header">
        <span className="step-count">1/5</span>
        <h2>Basic Settinss </h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1>Company Details</h1>
          <p>Enter you company name, start date and financial year starts</p>
        </div>

        <form onSubmit={handleSubmit} className="company-form">
          <div className="form-group">
            <label htmlFor="company_name">
              Company Name<span className="required">*</span>
            </label>
            <input
              type="text"
              id="company_name"
              value={formData.company_name}
              onChange={(e) => setFormData({...formData, company_name: e.target.value})}
              placeholder="Enter company name"
              required
            />
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="company_start">
                Company Start Date<span className="required">*</span>
              </label>
              <input
                type="date"
                id="company_start"
                value={formData.company_start}
                onChange={(e) => setFormData({...formData, company_start: e.target.value})}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="financial_year">
                Financial Year Starts<span className="required">*</span>
              </label>
              <select
                id="financial_year"
                value={formData.financial_year}
                onChange={(e) => setFormData({...formData, financial_year: e.target.value})}
                required
              >
                {months.map(month => (
                  <option key={month} value={month}>{month}</option>
                ))}
              </select>
            </div>
          </div>

          {error && <div className="error-message">{error}</div>}

          <div className="form-actions">
            <button 
              type="submit" 
              className="button button-primary"
              disabled={loading}
            >
              {loading ? 'Saving...' : 'Next'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CompanyDetails;