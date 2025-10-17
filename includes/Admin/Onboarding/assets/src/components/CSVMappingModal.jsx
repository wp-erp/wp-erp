import { useState, useEffect } from 'react';

const CSVMappingModal = ({ file, onClose, onSuccess }) => {
  const [csvColumns, setCsvColumns] = useState([]);
  const [fieldMappings, setFieldMappings] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [importedCount, setImportedCount] = useState(0);

  // Profile fields and required fields from WordPress (based on existing sample CSV)
  const profileFields = [
    'employee_id',
    'first_name',
    'middle_name',
    'last_name',
    'user_email',
    'designation',
    'department',
    'location',
    'hiring_source',
    'hiring_date',
    'date_of_birth',
    'reporting_to',
    'pay_rate',
    'type',
    'pay_type',
    'status',
    'other_email',
    'phone',
    'work_phone',
    'mobile',
    'address',
    'gender',
    'marital_status',
    'nationality',
    'driving_license',
    'hobbies',
    'user_url',
    'description',
    'street_1',
    'street_2',
    'city',
    'country',
    'state',
    'postal_code'
  ];

  const requiredFields = ['first_name', 'last_name', 'user_email'];

  useEffect(() => {
    if (file) {
      readCsvColumns(file);
    }
  }, [file]);

  const readCsvColumns = (file) => {
    const reader = new FileReader();
    const first5000 = file.slice(0, 5000);

    reader.readAsText(first5000);
    reader.onload = (e) => {
      const csv = reader.result;
      const lines = csv.split('\n');
      const columnNamesLine = lines[0];
      const columns = columnNamesLine.split(',').map((col) => col.trim().replace(/"/g, ''));

      setCsvColumns(columns);

      // Auto-match columns based on name similarity
      const autoMappings = {};
      profileFields.forEach((field) => {
        const matchingColumnIndex = columns.findIndex((col) => {
          const normalizedCol = col.toLowerCase().replace(/[_\s]/g, '');
          const normalizedField = field.toLowerCase().replace(/[_\s]/g, '');
          return normalizedCol === normalizedField;
        });

        if (matchingColumnIndex !== -1) {
          autoMappings[field] = matchingColumnIndex;
        }
      });

      setFieldMappings(autoMappings);
    };
  };

  const handleMappingChange = (field, columnIndex) => {
    setFieldMappings((prev) => ({
      ...prev,
      [field]: columnIndex === '' ? undefined : parseInt(columnIndex)
    }));
  };

  const validateMappings = () => {
    for (const field of requiredFields) {
      if (fieldMappings[field] === undefined) {
        return false;
      }
    }
    return true;
  };

  const handleImport = async () => {
    if (!validateMappings()) {
      alert('Please map all required fields (marked with *)');
      return;
    }

    setIsLoading(true);

    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'erp_import_csv');
    formData.append('type', 'employee');
    formData.append('csv_file', file);
    formData.append('_wpnonce', window.wpErpOnboarding?.importNonce || '');

    // Add field mappings
    Object.entries(fieldMappings).forEach(([field, columnIndex]) => {
      if (columnIndex !== undefined) {
        formData.append(`fields[${field}]`, columnIndex);
      }
    });

    try {
      const response = await fetch(window.wpErpOnboarding?.adminUrl + 'admin-ajax.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Extract count from message (e.g., "5 items have been imported successfully")
        const match = result.data?.match(/(\d+)/);
        const count = match ? match[1] : '0';

        setImportedCount(count);
        setShowSuccess(true);
      } else {
        alert(result.data || 'Import failed. Please try again.');
      }
    } catch (error) {
      console.error('Import error:', error);
      alert('Import failed. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleContinue = () => {
    onSuccess();
  };

  const formatFieldName = (field) => {
    return field
      .split('_')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  if (showSuccess) {
    return (
      <div className="erp-modal">
        <div className="erp-modal-overlay" onClick={onClose}></div>
        <div className="erp-modal-content">
          <div className="erp-modal-body">
            <div className="erp-import-success">
              <div className="erp-success-icon">
                <svg width="54" height="55" viewBox="0 0 54 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="27" cy="27.5" r="27" fill="#10B981" fillOpacity="0.1"/>
                  <path d="M18 27.5L24 33.5L36 21.5" stroke="#10B981" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                </svg>
              </div>
              <h3 className="text-xl font-medium text-gray-900 mb-2">Successfully Imported</h3>
              <p className="text-gray-600 text-sm">{importedCount} employees have been imported</p>
            </div>
          </div>
          <div className="erp-modal-footer">
            <button type="button" className="erp-btn-continue-setup" onClick={handleContinue}>
              Continue →
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="erp-modal">
      <div className="erp-modal-overlay" onClick={onClose}></div>
      <div className="erp-modal-content">
        <div className="erp-modal-header">
          <h2 className="text-lg font-semibold text-gray-900">Map Properties</h2>
          <button type="button" className="erp-modal-close" onClick={onClose}>
            &times;
          </button>
        </div>

        <div className="erp-modal-body">
          <div className="erp-modal-tabs">
            <div className="erp-tab active">
              Columns <span className="erp-column-count">({csvColumns.length})</span>
            </div>
            <div className="erp-tab">
              Profile Field
            </div>
          </div>

          <div className="erp-tab-content">
            <div className="erp-columns-tab">
              <table className="erp-mapping-table">
                <tbody>
                  {profileFields.map((field) => {
                    const isRequired = requiredFields.includes(field);
                    return (
                      <tr key={field}>
                        <td>
                          <label>
                            {formatFieldName(field)}
                            {isRequired && <span className="text-red-500 ml-1">*</span>}
                          </label>
                        </td>
                        <td>
                          <select
                            className="erp-field-select"
                            value={fieldMappings[field] !== undefined ? fieldMappings[field] : ''}
                            onChange={(e) => handleMappingChange(field, e.target.value)}
                            required={isRequired}
                          >
                            <option value="">
                              {formatFieldName(field)}
                            </option>
                            {csvColumns.map((col, index) => (
                              <option key={index} value={index}>
                                {col}
                              </option>
                            ))}
                          </select>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div className="erp-modal-footer">
          <button type="button" className="erp-btn-cancel" onClick={onClose} disabled={isLoading}>
            Cancel
          </button>
          <button
            type="button"
            className="erp-btn-import"
            onClick={handleImport}
            disabled={isLoading}
          >
            {isLoading ? (
              <>
                <span className="erp-loading-spinner"></span> Importing...
              </>
            ) : (
              'Import Employee'
            )}
          </button>
        </div>
      </div>
    </div>
  );
};

export default CSVMappingModal;

