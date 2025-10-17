import { useState } from 'react';

const ModuleStep = ({ onNext, onBack, initialData = {} }) => {
  const [formData, setFormData] = useState({
    enableLeaveManagement: initialData.enableLeaveManagement ?? true,
    workingDays: initialData.workingDays || {
      monday: true,
      tuesday: true,
      wednesday: true,
      thursday: true,
      friday: true,
      saturday: false,
      sunday: false,
    },
    workingHours: initialData.workingHours || {
      start: '09:00',
      end: '17:00',
    },
  });

  const handleToggle = (field) => {
    setFormData((prev) => ({
      ...prev,
      [field]: !prev[field],
    }));
  };

  const handleDayToggle = (day) => {
    setFormData((prev) => ({
      ...prev,
      workingDays: {
        ...prev.workingDays,
        [day]: !prev.workingDays[day],
      },
    }));
  };

  const handleTimeChange = (field, value) => {
    setFormData((prev) => ({
      ...prev,
      workingHours: {
        ...prev.workingHours,
        [field]: value,
      },
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onNext(formData);
  };

  const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

  return (
    <div>
      {/* Matches erp-setup-content from setup.css - 640px constraint with auto margins */}
      <div className="max-w-[640px] mx-auto overflow-visible">
        {/* Heading - matches h1 from setup.css */}
        <h1 className="text-black text-[30px] font-normal leading-9 text-center m-0 mb-3">
          Leave and Workday Setup
        </h1>
        {/* Subtitle - matches .subtitle from setup.css */}
        <p className="text-center text-slate-500 text-base m-0 mb-16 leading-6">
          Configure your company's working schedule
        </p>

        <form onSubmit={handleSubmit} className="mb-0">
          {/* Leave Management Toggle - matches erp-toggle-section from setup.css */}
          <div className="bg-[#e0f2fe] rounded-lg p-8 mt-8 mb-6">
            <div className="flex items-center gap-4">
              <div className="flex-shrink-0 flex items-center justify-center">
                <input
                  type="checkbox"
                  id="enableLeaveManagement"
                  checked={formData.enableLeaveManagement}
                  onChange={() => handleToggle('enableLeaveManagement')}
                  className="absolute opacity-0"
                />
                <label htmlFor="enableLeaveManagement" className="inline-flex items-center justify-center cursor-pointer select-none">
                  <span className={`relative w-9 h-4 top-0.5 rounded-lg transition-colors duration-300 ${
                    formData.enableLeaveManagement ? 'bg-blue-500' : 'bg-[#cbd5e1]'
                  }`}>
                    <span className={`absolute top-[-2px] w-5 h-5 bg-white border border-[#cbd5e1] rounded-full transition-all duration-300 ease-in-out z-10 ${
                      formData.enableLeaveManagement ? 'left-4' : 'left-[-2px]'
                    }`}></span>
                  </span>
                </label>
              </div>
              <div className="flex-1">
                <p className="text-slate-500 text-sm leading-5 m-0">
                  Allow employees to request and track leave
                </p>
              </div>
            </div>
          </div>

          {/* Working Days - matches erp-workday-row from setup.css */}
          <div className="mb-6">
            <label className="label mb-4">Working Days</label>
            <div className="flex flex-col gap-4">
              {days.map((day) => (
                <div key={day} className="flex items-center justify-between py-3">
                  <div className="font-medium min-w-[100px] capitalize">
                    {day}
                  </div>
                  <div className="flex gap-2">
                    <label className="inline-block">
                      <input
                        type="radio"
                        name={`day-${day}`}
                        checked={formData.workingDays[day]}
                        onChange={() => !formData.workingDays[day] && handleDayToggle(day)}
                        className="hidden"
                      />
                      <span className={`inline-block px-4 py-2 rounded-md border border-[#cbd5e1] cursor-pointer transition-all duration-200 ${
                        formData.workingDays[day] ? 'bg-blue-500 text-white' : 'bg-white text-black'
                      }`}>
                        Working
                      </span>
                    </label>
                    <label className="inline-block">
                      <input
                        type="radio"
                        name={`day-${day}`}
                        checked={!formData.workingDays[day]}
                        onChange={() => formData.workingDays[day] && handleDayToggle(day)}
                        className="hidden"
                      />
                      <span className={`inline-block px-4 py-2 rounded-md border border-[#cbd5e1] cursor-pointer transition-all duration-200 ${
                        !formData.workingDays[day] ? 'bg-blue-500 text-white' : 'bg-white text-black'
                      }`}>
                        Non-working
                      </span>
                    </label>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Working Hours - matches erp-form-row from setup.css */}
          <div className="mb-6">
            <label className="label mb-4">Working Hours</label>
            <div className="grid grid-cols-2 gap-5">
              <div>
                <label htmlFor="startTime" className="block text-sm text-slate-600 mb-2">
                  Start Time
                </label>
                <input
                  type="time"
                  id="startTime"
                  value={formData.workingHours.start}
                  onChange={(e) => handleTimeChange('start', e.target.value)}
                  className="input"
                />
              </div>
              <div>
                <label htmlFor="endTime" className="block text-sm text-slate-600 mb-2">
                  End Time
                </label>
                <input
                  type="time"
                  id="endTime"
                  value={formData.workingHours.end}
                  onChange={(e) => handleTimeChange('end', e.target.value)}
                  className="input"
                />
              </div>
            </div>
          </div>

          {/* Button Container - matches erp-button-container with exact margin */}
          <div className="mt-138.8px text-center flex justify-between">
            <button type="button" onClick={onBack} className="btn-secondary">
              Back
            </button>
            <button type="submit" className="btn-primary no-underline">
              Continue
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ModuleStep;
