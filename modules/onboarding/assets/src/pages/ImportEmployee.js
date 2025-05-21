import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { __ } from '@wordpress/i18n';

const ImportEmployee = () => {
  const navigate = useNavigate();
  const [step, setStep] = useState(1);
  const [file, setFile] = useState(null);
  const [fileName, setFileName] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [importCount, setImportCount] = useState(0);
  const [error, setError] = useState(null);
  const [employeeFields, setEmployeeFields] = useState({});
  const [mappingFields, setMappingFields] = useState({});
  const [csvHeaders, setCsvHeaders] = useState([]);

  useEffect(() => {
    fetchEmployeeFields();
  }, []);

  const fetchEmployeeFields = async () => {
    try {
      if (!ErpOnboard?.nonce) {
        setError(__('Authentication error. Please refresh the page.', 'erp'));
        return;
      }

      const response = await axios.get(`${ErpOnboard.restUrl}erp/v1/onboarding/import-employees`, {
        headers: {
          'X-WP-Nonce': ErpOnboard.nonce,
          'Content-Type': 'application/json'
        }
      });
      setEmployeeFields(response.data);
    } catch (err) {
      console.error('Error fetching employee fields:', err);
      if (err.response?.status === 401) {
        setError(__('Your session has expired. Please refresh the page.', 'erp'));
      } else if (err.response?.status === 403) {
        setError(__('You do not have permission to access this feature.', 'erp'));
      } else {
        setError(err.response?.data?.message || __('Failed to fetch employee fields', 'erp'));
      }
    }
  };

  const handleFileChange = (e) => {
    const selectedFile = e.target.files[0];
    if (selectedFile && selectedFile.type === 'text/csv') {
      setFile(selectedFile);
      setFileName(selectedFile.name);
      readCsvHeaders(selectedFile);
      setStep(2);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    const droppedFile = e.dataTransfer.files[0];
    if (droppedFile && droppedFile.type === 'text/csv') {
      setFile(droppedFile);
      setFileName(droppedFile.name);
      readCsvHeaders(droppedFile);
      setStep(2);
    }
  };

  const readCsvHeaders = (file) => {
    const reader = new FileReader();
    reader.onload = (e) => {
      const text = e.target.result;
      const headers = text.split('\n')[0].split(',').map(h => h.trim());
      setCsvHeaders(headers);
    };
    reader.readAsText(file);
  };

  const handleMappingChange = (field, value) => {
    setMappingFields(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleImport = async () => {
    setLoading(true);
    setError(null);

    if (!ErpOnboard?.nonce) {
      setError(__('Authentication error. Please refresh the page.', 'erp'));
      setLoading(false);
      return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('mapping', JSON.stringify(mappingFields));

    try {
      const response = await axios.post(`${ErpOnboard.restUrl}erp/v1/onboarding/import-employees`, formData, {
        headers: {
          'X-WP-Nonce': ErpOnboard.nonce,
          'Content-Type': 'multipart/form-data'
        }
      });
      setSuccess(true);
      setImportCount(response.data.imported || 0);
      setStep(4);
    } catch (error) {
      if (error.response?.status === 401) {
        setError(__('Your session has expired. Please refresh the page.', 'erp'));
      } else if (error.response?.status === 403) {
        setError(__('You do not have permission to import employees.', 'erp'));
      } else {
        setError(error.response?.data?.message || __('Import failed. Please try again.', 'erp'));
      }
    } finally {
      setLoading(false);
    }
  };

  const downloadSample = () => {
    window.location.href = `${ErpOnboard.restUrl}erp/v1/onboarding/sample-csv`;
  };

  const renderStep1 = () => (
    <div className="erp-onboarding-page import-employee">
      <div className="page-header">
        <span className="step-count">3/5</span>
        <h2>{__('Import Employee', 'erp')}</h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1>{__('Import Your Employees', 'erp')}</h1>
          <p>{__('Upload a CSV file to import your employees.', 'erp')}</p>
        </div>

        <div
          className="upload-area"
          onDrop={handleDrop}
          onDragOver={(e) => e.preventDefault()}
        >
          <div className="upload-icon">📁</div>
          <h3>{__('Drag & Drop CSV File', 'erp')}</h3>
          <p>{__('or', 'erp')}</p>
          <input
            type="file"
            id="file-upload"
            accept=".csv"
            onChange={handleFileChange}
            style={{ display: 'none' }}
          />
          <label htmlFor="file-upload" className="button button-secondary">
            {__('Choose File', 'erp')}
          </label>
          <button onClick={downloadSample} className="download-sample">
            {__('Download Sample CSV', 'erp')}
          </button>
        </div>
      </div>
    </div>
  );

  const renderStep2 = () => (
    <div className="erp-onboarding-page import-employee">
      <div className="page-header">
        <span className="step-count">3/5</span>
        <h2>{__('Import Employee', 'erp')}</h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1>{__('File Uploaded', 'erp')}</h1>
          <p>{__('Map your CSV columns to employee fields.', 'erp')}</p>
        </div>

        <div className="file-info">
          <div className="file-name">
            <span className="icon">📄</span>
            {fileName}
          </div>
          <button
            className="button button-secondary"
            onClick={() => setStep(1)}
          >
            {__('Change File', 'erp')}
          </button>
        </div>

        <div className="mapping-section">
          <h3>{__('Map Fields', 'erp')}</h3>
          <div className="mapping-table">
            <div className="mapping-header">
              <span>{__('CSV Column', 'erp')}</span>
              <span>{__('Employee Field', 'erp')}</span>
            </div>
            {csvHeaders.map((header, index) => (
              <div key={index} className="mapping-row">
                <div className="csv-column">{header}</div>
                <select
                  value={mappingFields[header] || ''}
                  onChange={(e) => handleMappingChange(header, e.target.value)}
                >
                  <option value="">{__('Select Field', 'erp')}</option>
                  {Object.entries(employeeFields).map(([category, fields]) => (
                    <optgroup key={category} label={category}>
                      {Object.entries(fields).map(([key, label]) => (
                        <option key={key} value={key}>{label}</option>
                      ))}
                    </optgroup>
                  ))}
                </select>
              </div>
            ))}
          </div>
        </div>

        <div className="form-actions">
          <button
            onClick={handleImport}
            className="button button-primary"
            disabled={loading}
          >
            {loading ? __('Importing...', 'erp') : __('Import Employees', 'erp')}
          </button>
        </div>
      </div>
    </div>
  );

  const renderStep4 = () => (
    <div className="erp-onboarding-page import-employee">
      <div className="page-header">
        <span className="step-count">3/5</span>
        <h2>{__('Import Employee', 'erp')}</h2>
      </div>

      <div className="page-content">
        <div className="success-message">
          <div className="success-icon">✅</div>
          <h2>{__('Import Successful', 'erp')}</h2>
          <p>{__('Successfully imported', 'erp')} {importCount} {__('employees', 'erp')}</p>
        </div>

        <div className="form-actions">
          <button
            onClick={() => navigate('/leave')}
            className="button button-primary"
          >
            {__('Next', 'erp')}
          </button>
        </div>
      </div>
    </div>
  );

  return (
    <>
      {step === 1 && renderStep1()}
      {step === 2 && renderStep2()}
      {step === 4 && renderStep4()}
      {error && <div className="error-message">{error}</div>}
    </>
  );
};

export default ImportEmployee;
