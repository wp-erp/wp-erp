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
    const fetchCompanySettings = async () => {
        try {
            const response = await axios.get(`${ErpOnboard.restUrl}erp/v1/onboarding/company`, {
                headers: {
                    'X-WP-Nonce': ErpOnboard.nonce
                }
            });
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
      await axios.put(`${ErpOnboard.restUrl}erp/v1/onboarding/company`, formData, {
        headers: {
          'X-WP-Nonce': ErpOnboard.nonce
        }
      });
      onComplete && onComplete();
      navigate('/department-designation');
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
        <h2 className="step-title">Basic Setting</h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1 className="section-title">Company Details</h1>
          <p className="section-description">Enter your company name, start date and financial year starts</p>
        </div>

        <form onSubmit={handleSubmit} className="company-form">
          <div className="form-group company-name-group">
            <label htmlFor="company_name">
              Company Name<span className="required">*</span>
            </label>
            <input
              type="text"
              id="company_name"
              value={formData.company_name}
              onChange={(e) => setFormData({...formData, company_name: e.target.value})}
              placeholder="Company Name"
              required
              className="form-control"
            />
          </div>

          <div className="form-row date-row">
            <div className="form-group date-group">
              <label htmlFor="company_start">
                Company Start Date<span className="required">*</span>
              </label>
              <input
                type="date"
                id="company_start"
                value={formData.company_start}
                onChange={(e) => setFormData({...formData, company_start: e.target.value})}
                required
                className="form-control"
              />
            </div>

            <div className="form-group month-group">
              <label htmlFor="financial_year">
                Financial Year Starts<span className="required">*</span>
              </label>
              <select
                id="financial_year"
                value={formData.financial_year}
                onChange={(e) => setFormData({...formData, financial_year: e.target.value})}
                required
                className="form-control"
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

      <style jsx="true">{`
        .erp-onboarding-page {
          max-width: 800px;
          margin: 0 auto;
          padding: 40px 20px;
        }

        .page-header {
          text-align: center;
          margin-bottom: 40px;
        }

        .step-count {
          font-size: 14px;
          color: #6B7280;
          margin-bottom: 8px;
          display: block;
        }

        .step-title {
          font-size: 24px;
          color: #111827;
          margin: 0;
        }

        .section-header {
          text-align: center;
          margin-bottom: 40px;
        }

        .section-title {
          font-size: 28px;
          color: #111827;
          margin: 0 0 12px;
        }

        .section-description {
          color: #6B7280;
          margin: 0;
        }

        .company-form {
          max-width: 600px;
          margin: 0 auto;
          background: #fff;
          padding: 32px;
          border-radius: 8px;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-group {
          margin-bottom: 24px;
        }

        .form-group label {
          display: block;
          margin-bottom: 8px;
          color: #374151;
          font-weight: 500;
        }

        .required {
          color: #DC2626;
          margin-left: 4px;
        }

        .form-control {
          width: 100%;
          padding: 10px 16px;
          border: 1px solid #D1D5DB;
          border-radius: 6px;
          font-size: 16px;
          transition: border-color 0.15s ease;
        }

        .form-control:focus {
          border-color: #2563EB;
          outline: none;
          box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .date-row {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 24px;
        }

        .form-actions {
          margin-top: 32px;
          text-align: right;
        }

        .button-primary {
          background: #2563EB;
          color: white;
          padding: 12px 24px;
          border: none;
          border-radius: 6px;
          font-size: 16px;
          font-weight: 500;
          cursor: pointer;
          transition: background-color 0.15s ease;
        }

        .button-primary:hover {
          background: #1D4ED8;
        }

        .button-primary:disabled {
          background: #93C5FD;
          cursor: not-allowed;
        }

        .error-message {
          color: #DC2626;
          margin-top: 8px;
          font-size: 14px;
        }
      `}</style>
    </div>
  );
};

export default CompanyDetails;
