import { useState } from 'react';
import CSVMappingModal from '../CSVMappingModal';

const ImportStep = ({ onNext, initialData = {} }) => {
  const [file, setFile] = useState(null);
  const [dragActive, setDragActive] = useState(false);
  const [uploadStatus, setUploadStatus] = useState('');
  const [showModal, setShowModal] = useState(false);

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
      // Show modal when file is uploaded
      setShowModal(true);
    } else {
      setUploadStatus('error');
      setFile(null);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Just skip to next step
    onNext({ skipImport: true });
  };

  const handleModalClose = () => {
    setShowModal(false);
  };

  const handleImportSuccess = () => {
    setShowModal(false);
    // Proceed to next step after successful import
    onNext({ skipImport: false });
  };

  const downloadSample = () => {
    // Use the old Vue-based dynamic CSV URL with nonce
    const sampleUrl = window.wpErpOnboarding?.sampleCsvUrl;
    if (sampleUrl) {
      window.location.href = sampleUrl;
    }
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
                className={`flex flex-col items-center justify-center p-12 bg-white rounded-xl cursor-pointer transition-all duration-200 ${
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
                <svg width="54" height="42" viewBox="0 0 54 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M27 33.1992L27 15.1992M27 15.1992L35 23.1992M27 15.1992L19 23.1992M13 41.1992C6.37258 41.1992 1 35.8266 1 29.1992C1 23.8847 4.4548 19.3771 9.24107 17.7997C9.08279 16.9571 9 16.0878 9 15.1992C9 7.46723 15.268 1.19922 23 1.19922C29.4833 1.19922 34.9373 5.60616 36.5298 11.5879C37.3078 11.3356 38.138 11.1992 39 11.1992C43.4183 11.1992 47 14.7809 47 19.1992C47 20.1276 46.8419 21.019 46.5511 21.8481C50.3209 23.2804 53 26.927 53 31.1992C53 36.7221 48.5228 41.1992 43 41.1992H13Z" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
                    <span className="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 border-0 text-sm font-medium cursor-pointer transition-all duration-200 hover:bg-blue-50 rounded">
                      Choose File
                      <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.900391 12.7988C0.900391 12.357 1.25856 11.9988 1.70039 11.9988H11.3004C11.7422 11.9988 12.1004 12.357 12.1004 12.7988C12.1004 13.2407 11.7422 13.5988 11.3004 13.5988H1.70039C1.25856 13.5988 0.900391 13.2407 0.900391 12.7988ZM3.53471 4.56451C3.22229 4.25209 3.22229 3.74556 3.53471 3.43314L5.93471 1.03314C6.08473 0.883114 6.28822 0.798828 6.50039 0.798828C6.71256 0.798828 6.91605 0.883114 7.06608 1.03314L9.46608 3.43314C9.7785 3.74556 9.7785 4.25209 9.46608 4.56451C9.15366 4.87693 8.64713 4.87693 8.33471 4.56451L7.30039 3.5302V9.59883C7.30039 10.0407 6.94222 10.3988 6.50039 10.3988C6.05856 10.3988 5.70039 10.0407 5.70039 9.59883L5.70039 3.5302L4.66608 4.56451C4.35366 4.87693 3.84713 4.87693 3.53471 4.56451Z" fill="#6366F1"/>
                      </svg>
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
          <div className="mt-[138.8px] text-center mt-8">
            <button type="submit" className="btn-primary no-underline">
              Skip for Now
            </button>
          </div>
        </form>
      </div>

      {/* CSV Mapping Modal */}
      {showModal && file && (
        <CSVMappingModal
          file={file}
          onClose={handleModalClose}
          onSuccess={handleImportSuccess}
        />
      )}
    </div>
  );
};

export default ImportStep;
