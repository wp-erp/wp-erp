import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const WorkdaySetup = () => {
    const [workdays, setWorkdays] = useState({
        monday: 'full',
        tuesday: 'full',
        wednesday: 'full',
        thursday: 'full',
        friday: 'full',
        saturday: 'non-working',
        sunday: 'non-working'
    });
    const [options, setOptions] = useState({});
    const [days, setDays] = useState({});
    const navigate = useNavigate();

    const fetchWorkdays = async () => {
        try {
            const response = await axios.get(`${ErpOnboard.restUrl}erp/v1/onboarding/workdays`, {
                headers: {
                    'X-WP-Nonce': ErpOnboard.nonce
                }
            });
            setWorkdays(response.data.working_days);
            setOptions(response.data.options);
            setDays(response.data.days);
        } catch (err) {
            console.error('Error fetching workdays:', err);
        }
    };

    const handleSave = async () => {
        try {
            await axios.post(`${ErpOnboard.restUrl}erp/v1/onboarding/workdays`, workdays, {
                headers: {
                    'X-WP-Nonce': ErpOnboard.nonce
                }
            });
            navigate('/leave');
        } catch (err) {
            console.error('Error saving workdays:', err);
        }
    };

    const handleWorkdayChange = (day, type) => {
        setWorkdays(prev => ({
            ...prev,
            [day]: type
        }));
    };

    const getDayLabel = (day) => {
        const labels = {
            monday: __('Monday', 'erp'),
            tuesday: __('Tuesday', 'erp'),
            wednesday: __('Wednesday', 'erp'),
            thursday: __('Thursday', 'erp'),
            friday: __('Friday', 'erp'),
            saturday: __('Saturday', 'erp'),
            sunday: __('Sunday', 'erp')
        };
        return labels[day];
    };

    return (
        <div className="erp-workday-setup">
            <div className="workday-setup-header">
                <h2>{__('Workday Setup', 'erp')}</h2>
                <p>{__('Configure your organization\'s weekly work schedule', 'erp')}</p>
            </div>

            <div className="workdays-container">
                {Object.entries(workdays).map(([day, type]) => (
                    <div key={day} className="workday-item">
                        <div className="day-label">{getDayLabel(day)}</div>
                        <div className="day-settings">
                            <button
                                className={`day-type-button ${type === 'full' ? 'active' : ''}`}
                                onClick={() => handleWorkdayChange(day, 'full')}
                            >
                                {__('Full Day', 'erp')}
                            </button>
                            <button
                                className={`day-type-button ${type === 'half' ? 'active' : ''}`}
                                onClick={() => handleWorkdayChange(day, 'half')}
                            >
                                {__('Half Day', 'erp')}
                            </button>
                            <button
                                className={`day-type-button ${type === 'non-working' ? 'active' : ''}`}
                                onClick={() => handleWorkdayChange(day, 'non-working')}
                            >
                                {__('Non-working Day', 'erp')}
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            <div className="form-actions">
                <button className="button button-primary" onClick={handleSave}>
                    {__('Save Settings', 'erp')}
                </button>
            </div>
        </div>
    );
};

export default WorkdaySetup;
