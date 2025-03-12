import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

const CsvImportSteps = () => {
    const [currentStep, setCurrentStep] = useState(1);
    const [file, setFile] = useState(null);
    const [mappedFields, setMappedFields] = useState({});

    const steps = [
        {
            title: __('Welcome to CSV Import', 'erp'),
            description: __('Import your employee data easily with our CSV import tool. Follow these simple steps to ensure a smooth data migration.', 'erp'),
            content: (
                <div className="csv-welcome-step">
                    <h3>{__('Before You Begin', 'erp')}</h3>
                    <ul>
                        <li>{__('Prepare your CSV file with employee data', 'erp')}</li>
                        <li>{__('Download our template for correct formatting', 'erp')}</li>
                        <li>{__('Ensure required fields are filled', 'erp')}</li>
                        <li>{__('Review data for accuracy', 'erp')}</li>
                    </ul>
                    <button className="button button-primary" onClick={() => setCurrentStep(2)}>
                        {__('Get Started', 'erp')}
                    </button>
                </div>
            )
        },
        {
            title: __('Download Template', 'erp'),
            description: __('Use our pre-formatted template to ensure your data is structured correctly.', 'erp'),
            content: (
                <div className="csv-template-step">
                    <div className="template-download-box">
                        <i className="fas fa-file-csv"></i>
                        <h4>{__('Sample CSV Template', 'erp')}</h4>
                        <p>{__('Download this template and add your employee data', 'erp')}</p>
                        <button className="button button-secondary">
                            <i className="fas fa-download"></i>
                            {__('Download Template', 'erp')}
                        </button>
                    </div>
                    <div className="step-navigation">
                        <button className="button" onClick={() => setCurrentStep(1)}>{__('Back', 'erp')}</button>
                        <button className="button button-primary" onClick={() => setCurrentStep(3)}>{__('Next', 'erp')}</button>
                    </div>
                </div>
            )
        },
        {
            title: __('Upload CSV File', 'erp'),
            description: __('Upload your CSV file containing employee data.', 'erp'),
            content: (
                <div className="csv-upload-step">
                    <div className="upload-zone" 
                         onDrop={(e) => {
                             e.preventDefault();
                             setFile(e.dataTransfer.files[0]);
                         }}
                         onDragOver={(e) => e.preventDefault()}>
                        <i className="fas fa-cloud-upload-alt"></i>
                        <h4>{__('Drag and Drop Your CSV File Here', 'erp')}</h4>
                        <p>{__('or', 'erp')}</p>
                        <input type="file" 
                               accept=".csv" 
                               onChange={(e) => setFile(e.target.files[0])} 
                               style={{display: 'none'}} 
                               id="csv-file-input" />
                        <label className="button" htmlFor="csv-file-input">
                            {__('Browse File', 'erp')}
                        </label>
                        {file && <p className="file-name">{file.name}</p>}
                    </div>
                    <div className="step-navigation">
                        <button className="button" onClick={() => setCurrentStep(2)}>{__('Back', 'erp')}</button>
                        <button className="button button-primary" 
                                onClick={() => setCurrentStep(4)} 
                                disabled={!file}>
                            {__('Next', 'erp')}
                        </button>
                    </div>
                </div>
            )
        },
        {
            title: __('Map Fields', 'erp'),
            description: __('Match your CSV columns with the corresponding system fields.', 'erp'),
            content: (
                <div className="csv-mapping-step">
                    <div className="field-mapping-container">
                        <table className="wp-list-table widefat fixed">
                            <thead>
                                <tr>
                                    <th>{__('CSV Column', 'erp')}</th>
                                    <th>{__('System Field', 'erp')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {/* Mapping fields will be dynamically populated */}
                            </tbody>
                        </table>
                    </div>
                    <div className="step-navigation">
                        <button className="button" onClick={() => setCurrentStep(3)}>{__('Back', 'erp')}</button>
                        <button className="button button-primary" 
                                onClick={() => setCurrentStep(5)} 
                                disabled={Object.keys(mappedFields).length === 0}>
                            {__('Next', 'erp')}
                        </button>
                    </div>
                </div>
            )
        },
        {
            title: __('Preview & Validate', 'erp'),
            description: __('Review your data and check for any potential issues before importing.', 'erp'),
            content: (
                <div className="csv-preview-step">
                    <div className="preview-container">
                        <div className="validation-summary">
                            <h4>{__('Validation Results', 'erp')}</h4>
                            {/* Validation results will be shown here */}
                        </div>
                        <div className="data-preview">
                            <h4>{__('Data Preview', 'erp')}</h4>
                            <table className="wp-list-table widefat fixed">
                                {/* Preview data will be shown here */}
                            </table>
                        </div>
                    </div>
                    <div className="step-navigation">
                        <button className="button" onClick={() => setCurrentStep(4)}>{__('Back', 'erp')}</button>
                        <button className="button button-primary">{__('Start Import', 'erp')}</button>
                    </div>
                </div>
            )
        }
    ];

    return (
        <div className="erp-csv-import-wizard">
            <div className="step-indicator">
                {steps.map((step, index) => (
                    <div key={index} className={`step ${currentStep === index + 1 ? 'active' : ''}`}>
                        <div className="step-number">{index + 1}</div>
                        <div className="step-title">{step.title}</div>
                    </div>
                ))}
            </div>
            <div className="step-content">
                <h2>{steps[currentStep - 1].title}</h2>
                <p className="step-description">{steps[currentStep - 1].description}</p>
                {steps[currentStep - 1].content}
            </div>
        </div>
    );
};

export default CsvImportSteps;