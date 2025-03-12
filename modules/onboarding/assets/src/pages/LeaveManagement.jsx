import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';

const LeaveManagement = () => {
    const navigate = useNavigate();
    const [leaveYears, setLeaveYears] = useState([
        { id: 1, startDate: '', endDate: '' }
    ]);
    const [generateDefaultPolicies, setGenerateDefaultPolicies] = useState(true);
    const validateDates = (id, field, value) => {
        const year = leaveYears.find(y => y.id === id);
        if (!year) return true;

        if (field === 'startDate' && year.endDate) {
            return new Date(value) <= new Date(year.endDate);
        } else if (field === 'endDate' && year.startDate) {
            return new Date(value) >= new Date(year.startDate);
        }
        return true;
    };

    const addLeaveYear = () => {
        setLeaveYears([...leaveYears, {
            id: leaveYears.length + 1,
            startDate: '',
            endDate: ''
        }]);
    };

    const removeLeaveYear = (id) => {
        if (leaveYears.length > 1) {
            setLeaveYears(leaveYears.filter(year => year.id !== id));
        }
    };

    const handleDateChange = (id, field, value) => {
        if (!validateDates(id, field, value)) {
            alert(__('End date must be after start date', 'erp'));
            return;
        }
        setLeaveYears(leaveYears.map(year => {
            if (year.id === id) {
                return { ...year, [field]: value };
            }
            return year;
        }));
    };

    const submit =  () => {
        // setTimeout(() => {
            navigate('/workdays');
        //   }, 2000);
      };
    

    return (
        <div className="erp-leave-management">
            <div className="leave-management-header">
                <h2>{__('Leave Management', 'erp')}</h2>
                <p>{__('Configure your organization\'s leave year settings', 'erp')}</p>
            </div>

            <div className="leave-years-container">
                {leaveYears.map((year) => (
                    <div key={year.id} className="leave-year-item">
                        <div className="leave-year-header">
                            <h4>{__('Leave Year', 'erp')} {year.id}</h4>
                            {leaveYears.length > 1 && (
                                <button 
                                    className="remove-year"
                                    onClick={() => removeLeaveYear(year.id)}
                                >
                                    <i className="fas fa-times"></i>
                                </button>
                            )}
                        </div>
                        <div className="date-inputs">
                            <div className="input-group">
                                <label>{__('Start Date', 'erp')}</label>
                                <input
                                    type="date"
                                    value={year.startDate}
                                    onChange={(e) => handleDateChange(year.id, 'startDate', e.target.value)}
                                    required
                                />
                            </div>
                            <div className="input-group">
                                <label>{__('End Date', 'erp')}</label>
                                <input
                                    type="date"
                                    value={year.endDate}
                                    onChange={(e) => handleDateChange(year.id, 'endDate', e.target.value)}
                                    required
                                />
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <button 
                className="add-leave-year button button-secondary"
                onClick={addLeaveYear}
            >
                <i className="fas fa-plus"></i>
                {__('Add Another Leave Year', 'erp')}
            </button>

            <div className="generate-policies-option">
                <label>
                    <input
                        type="checkbox"
                        checked={generateDefaultPolicies}
                        onChange={(e) => setGenerateDefaultPolicies(e.target.checked)}
                    />
                    {__('Generate pre-default leave policies for the current year', 'erp')}
                </label>
            </div>

            <div className="form-actions">
                <button className="button button-primary" onClick={submit}>
                    {__('Save Settings', 'erp')}
                </button>
            </div>
        </div>
    );
};

export default LeaveManagement;