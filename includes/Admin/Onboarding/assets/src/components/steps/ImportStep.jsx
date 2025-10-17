import { useState } from 'react';

const ImportStep = ({ onNext, onBack, initialData = {} }) => {
  const [file, setFile] = useState(null);
  const [dragActive, setDragActive] = useState(false);
  const [uploadStatus, setUploadStatus] = useState('');

  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);

    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const handleChange = (e) => {
    e.preventDefault();
    if (e.target.files && e.target.files[0]) {
      handleFile(e.target.files[0]);
    }
  };

  const handleFile = (file) => {
    if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
      setFile(file);
      setUploadStatus('success');
    } else {
      setUploadStatus('error');
      setFile(null);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onNext({ file, skipImport: !file });
  };

  const downloadSample = () => {
    const sampleUrl = window.wpErpOnboarding?.sampleCsvUrl || '/wp-content/plugins/wp-erp/assets/sample/wperp_employee_list.csv';
    window.location.href = sampleUrl;
  };

  return (
    <div>
      {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
      <div className="max-w-640px mx-auto overflow-visible">
        {/* Heading - matches h1 from setup.css */}
        <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
          Import Employees
        </h1>
        {/* Subtitle - matches .subtitle from setup.css */}
        <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
          Upload a CSV file to import your employees (optional)
        </p>

        <form onSubmit={handleSubmit} className="mb-0">
          {/* CSV Upload Wrapper - matches erp-csv-upload-wrapper from setup.css */}
          <div className="csv-upload-wrapper mb-6">
            {/* File Upload Box - matches erp-csv-upload-label */}
            <div
              onDragEnter={handleDrag}
              onDragLeave={handleDrag}
              onDragOver={handleDrag}
              onDrop={handleDrop}
              className="mb-5"
            >
              <input
                type="file"
                accept=".csv"
                onChange={handleChange}
                className="hidden"
                id="csv-upload"
              />
              <label
                htmlFor="csv-upload"
                className={`flex flex-col items-center justify-center p-12 bg-white rounded-xl cursor-pointer transition-all duration-200 border-2 border-dashed ${
                  dragActive
                    ? 'border-blue-500 bg-white'
                    : uploadStatus === 'success'
                    ? 'border-green-500 bg-green-50'
                    : uploadStatus === 'error'
                    ? 'border-red-500 bg-red-50'
                    : 'border-gray-300 hover:border-gray-400 hover:bg-gray-50'
                }`}
              >
                {/* Upload Icon */}
                <div className="mb-6">
                  <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M24 32V16M24 16L18 22M24 16L30 22" stroke="#6B7280" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    <path d="M40 32V38C40 39.1046 39.1046 40 38 40H10C8.89543 40 8 39.1046 8 38V32" stroke="#6B7280" strokeWidth="2" strokeLinecap="round"/>
                  </svg>
                </div>

                {/* Upload Text */}
                {file ? (
                  <div className="text-center">
                    <h4 className="m-0 mb-1 text-gray-900 text-base font-semibold leading-6">
                      ✓ File uploaded successfully
                    </h4>
                    <p className="m-0 text-slate-500 text-sm leading-5 font-normal">
                      {file.name}
                    </p>
                  </div>
                ) : (
                  <div className="text-center">
                    <h4 className="m-0 mb-1 text-gray-900 text-base font-semibold leading-6">
                      Drag and drop your CSV file here
                    </h4>
                    <p className="m-0 mb-4 text-gray-500 text-sm leading-5">
                      or
                    </p>
                    {/* Choose File Button - matches erp-choose-file-btn */}
                    <span className="inline-flex items-center gap-2 px-3 py-2 bg-[#eef2ff] text-indigo-700 border-0 text-sm font-medium cursor-pointer transition-all duration-200 hover:bg-blue-50 rounded">
                      <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8 2v8M8 2L5 5M8 2l3 3M2 12h12"/>
                      </svg>
                      Choose File
                    </span>
                  </div>
                )}
                {uploadStatus === 'error' && (
                  <p className="text-red-600 text-sm mt-2 m-0">
                    Please upload a valid CSV file
                  </p>
                )}
              </label>
            </div>

            {/* Help Text - matches erp-csv-help-text */}
            <p className="m-0 text-gray-500 text-[13px] leading-5 text-center">
              Need a template?{' '}
              <button
                type="button"
                onClick={downloadSample}
                className="text-blue-500 no-underline font-medium bg-transparent border-0 cursor-pointer p-0 hover:text-blue-600 hover:underline"
              >
                Download Sample CSV
              </button>
            </p>
          </div>

          {/* Button Container - matches erp-button-container with exact margin */}
          <div className="mt-[138.8px] text-center flex justify-between">
            <button type="button" onClick={onBack} className="btn-secondary">
              Back
            </button>
            <button type="submit" className="btn-primary no-underline">
              {file ? 'Import & Continue' : 'Skip for Now'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ImportStep;
