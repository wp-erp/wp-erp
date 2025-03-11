import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const ImportEmployee = ({ onComplete }) => {
  const navigate = useNavigate();
  const [file, setFile] = useState(null);
  const [fileName, setFileName] = useState('');
  const [showMapping, setShowMapping] = useState(false);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [importCount, setImportCount] = useState(0);
  const [mappingFields, setMappingFields] = useState({
    first_name: '',
    middle_name: '',
    last_name: '',
    user_email: '',
    department: '',
    designation: ''
  });

  const handleFileChange = (e) => {
    const selectedFile = e.target.files[0];
    if (selectedFile && selectedFile.type === 'text/csv') {
      setFile(selectedFile);
      setFileName(selectedFile.name);
      setShowMapping(true);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    const droppedFile = e.dataTransfer.files[0];
    if (droppedFile && droppedFile.type === 'text/csv') {
      setFile(droppedFile);
      setFileName(droppedFile.name);
      setShowMapping(true);
    }
  };

  const handleMappingChange = (field, value) => {
    setMappingFields(prev => ({
      ...prev,
      [field]: value
    }));
  };

  const handleImport = async () => {
    setLoading(true);
    const formData = new FormData();
    formData.append('file', file);
    formData.append('mapping', JSON.stringify(mappingFields));

    try {
      const response = await axios.post('/wp-json/erp/v1/onboarding/import-employees', formData);
      setSuccess(true);
      setImportCount(response.data.count || 0);
      setTimeout(() => {
        navigate('/module-selection');
      }, 2000);
    } catch (error) {
      console.error('Import failed:', error);
    } finally {
      setLoading(false);
    }
  };

  const downloadSample = () => {
    window.location.href = '/wp-json/erp/v1/onboarding/sample-csv';
  };

  if (success) {
    return (
      <div className="erp-onboarding-page import-employee">
        <div className="page-header">
          <span className="step-count">3/5</span>
          <h2>Import Employee</h2>
        </div>
        <div className="success-message">
          <div className="success-icon">🎉</div>
          <h2>Successfully Imported</h2>
          <p>{importCount} employees has been imported</p>
        </div>
        <div className="form-actions">
          <button onClick={() => navigate('/module-selection')} className="button button-primary">
            Next
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="erp-onboarding-page import-employee">
      <div className="page-header">
        <span className="step-count">3/5</span>
        <h2>Import Employee</h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1>Import Employee</h1>
          <p>Enter you company name and start date.</p>
        </div>

        {!showMapping ? (
          <div 
            className="upload-area"
            onDrop={handleDrop}
            onDragOver={(e) => e.preventDefault()}
          >
            <div className="upload-icon">☁️</div>
            <h3>Upload a CSV file</h3>
            <p>Drag and Drop CSV file here or</p>
            <input
              type="file"
              id="file-upload"
              accept=".csv"
              onChange={handleFileChange}
              style={{ display: 'none' }}
            />
            <label htmlFor="file-upload" className="button button-secondary">
              Choose File
            </label>
            <button onClick={downloadSample} className="download-sample">
              Download Sample CSV
            </button>
          </div>
        ) : (
          <div className="mapping-section">
            <h3>Map Properties</h3>
            <div className="mapping-table">
              <div className="mapping-header">
                <span>Columns ({file?.name})</span>
                <span>Profile Field</span>
              </div>
              {Object.entries(mappingFields).map(([field, value]) => (
                <div key={field} className="mapping-row">
                  <label>{field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</label>
                  <select
                    value={value}
                    onChange={(e) => handleMappingChange(field, e.target.value)}
                  >
                    <option value="">Select Field</option>
                    <option value={field}>{field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</option>
                  </select>
                </div>
              ))}
            </div>
            <div className="mapping-actions">
              <button onClick={() => setShowMapping(false)} className="button">
                Cancel
              </button>
              <button 
                onClick={handleImport} 
                className="button button-primary"
                disabled={loading}
              >
                {loading ? 'Importing...' : 'Import Employee'}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ImportEmployee;