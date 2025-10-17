import { useState } from 'react';

const BasicStep = ({ onNext, initialData = {} }) => {
  const [formData, setFormData] = useState({
    companyName: initialData.companyName || '',
    companyStartDate: initialData.companyStartDate || '',
    financialYearStarts: initialData.financialYearStarts || 'january',
    ...initialData,
  });

  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors((prev) => ({ ...prev, [name]: '' }));
    }
  };

  const validate = () => {
    const newErrors = {};
    if (!formData.companyName?.trim()) {
      newErrors.companyName = 'Company name is required';
    }
    if (!formData.companyStartDate) {
      newErrors.companyStartDate = 'Company start date is required';
    }
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validate()) {
      onNext(formData);
    }
  };

  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  return (
    // Outer container - no width constraint
    <div>
      {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
      <div className="max-w-640px mx-auto overflow-visible">
        {/* Heading - matches h1 from setup.css */}
        <h1 className="text-black text-30px font-normal leading-9 text-center m-0 mb-3">
          Company Profile
        </h1>
        {/* Subtitle - matches .subtitle from setup.css */}
        <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
          Tell us about your company and financial year
        </p>

        {/* Form - matches erp-setup-form */}
        <form onSubmit={handleSubmit} className="mb-0">
          {/* Company Name - Full width */}
          <div className="mb-6">
            <label htmlFor="companyName" className="label">
              Company Name<span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="companyName"
              name="companyName"
              value={formData.companyName}
              onChange={handleChange}
              className={`input ${errors.companyName ? 'border-red-500' : ''}`}
              placeholder="Company Name"
            />
            {errors.companyName && (
              <p className="text-red-500 text-sm mt-1">{errors.companyName}</p>
            )}
          </div>

          {/* Two Column Layout - Company Start Date and Financial Year */}
          <div className="grid grid-cols-2 gap-5">
            {/* Company Start Date */}
            <div className="mb-6">
              <label htmlFor="companyStartDate" className="label">
                Company Start Date<span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                id="companyStartDate"
                name="companyStartDate"
                value={formData.companyStartDate}
                onChange={handleChange}
                className={`input ${errors.companyStartDate ? 'border-red-500' : ''}`}
                placeholder="June 01, 2000"
              />
              {errors.companyStartDate && (
                <p className="text-red-500 text-sm mt-1">{errors.companyStartDate}</p>
              )}
            </div>

            {/* Financial Year Starts */}
            <div className="mb-6">
              <label htmlFor="financialYearStarts" className="label">
                Financial Year Starts<span className="text-red-500">*</span>
              </label>
              <select
                id="financialYearStarts"
                name="financialYearStarts"
                value={formData.financialYearStarts}
                onChange={handleChange}
                className="select"
              >
                {months.map((month) => (
                  <option key={month.toLowerCase()} value={month.toLowerCase()}>
                    {month}
                  </option>
                ))}
              </select>
            </div>
          </div>

          {/* Button Container - matches erp-button-container with exact margin */}
          <div className="mt-138.8px text-center">
            <button type="submit" className="btn-primary no-underline">
              Next
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default BasicStep;
