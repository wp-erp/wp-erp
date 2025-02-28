import React, { useState } from 'react';

/**
 * ModuleSelection Page Component
 * 
 * Allows users to select which ERP modules they want to enable
 */
const ModuleSelection = ({ onComplete }) => {
  // State for selected modules
  const [selectedModules, setSelectedModules] = useState({
    accounting: true,
    hr: false,
    crm: false,
    inventory: false,
    project: false
  });
  
  // Available modules with their details
  const modules = [
    {
      id: 'accounting',
      name: 'Accounting',
      description: 'Manage financial transactions, invoices, expenses, and generate financial reports',
      icon: 'dashicons-chart-bar',
      features: ['Invoicing', 'Expense Tracking', 'Financial Reports', 'Tax Management']
    },
    {
      id: 'hr',
      name: 'Human Resources',
      description: 'Manage employees, departments, leave requests, and attendance',
      icon: 'dashicons-groups',
      features: ['Employee Records', 'Leave Management', 'Attendance', 'Department Organization']
    },
    {
      id: 'crm',
      name: 'Customer Relationship',
      description: 'Track customer interactions, manage contacts, and improve sales processes',
      icon: 'dashicons-businessman',
      features: ['Contact Management', 'Lead Tracking', 'Customer Communications', 'Sales Pipeline']
    },
    {
      id: 'inventory',
      name: 'Inventory Management',
      description: 'Track stock levels, manage products, and monitor inventory movements',
      icon: 'dashicons-archive',
      features: ['Stock Tracking', 'Purchase Orders', 'Product Catalog', 'Inventory Reports']
    },
    {
      id: 'project',
      name: 'Project Management',
      description: 'Plan projects, assign tasks, track progress, and manage resources',
      icon: 'dashicons-calendar-alt',
      features: ['Task Management', 'Project Timeline', 'Resource Allocation', 'Progress Tracking']
    }
  ];
  
  // Toggle module selection
  const toggleModule = (moduleId) => {
    setSelectedModules(prev => ({
      ...prev,
      [moduleId]: !prev[moduleId]
    }));
  };
  
  // Handle form submission
  const handleSubmit = () => {
    // In a real implementation, save the selected modules to your state management
    console.log('Selected modules:', selectedModules);
    
    // Move to next step
    onComplete();
  };
  
  // Count selected modules
  const selectedCount = Object.values(selectedModules).filter(Boolean).length;
  
  return (
    <div className="erp-onboarding-page module-selection-page">
      <div className="page-header">
        <h1>Choose Your Modules</h1>
        <p className="subtitle">Select the modules you want to enable in your ERP system</p>
      </div>
      
      <div className="page-content">
        <div className="module-selection-info">
          <p>
            Your ERP system is modular, allowing you to enable only the features you need.
            You can always enable additional modules later from the admin dashboard.
          </p>
          
          <div className="module-count">
            <span className="selected-count">{selectedCount}</span> modules selected
          </div>
        </div>
        
        <div className="modules-grid">
          {modules.map(module => (
            <div 
              key={module.id}
              className={`module-card ${selectedModules[module.id] ? 'selected' : ''}`}
              onClick={() => toggleModule(module.id)}
            >
              <div className="module-card-header">
                <div className="module-icon">
                  <span className={`dashicons ${module.icon}`}></span>
                </div>
                <div className="module-selection">
                  <input 
                    type="checkbox"
                    id={`module-${module.id}`}
                    checked={selectedModules[module.id] || false}
                    onChange={() => toggleModule(module.id)}
                  />
                  <label htmlFor={`module-${module.id}`} className="checkbox-label"></label>
                </div>
              </div>
              
              <div className="module-card-content">
                <h3 className="module-name">{module.name}</h3>
                <p className="module-description">{module.description}</p>
                
                <div className="module-features">
                  <h4>Key Features:</h4>
                  <ul>
                    {module.features.map((feature, index) => (
                      <li key={index}>{feature}</li>
                    ))}
                  </ul>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
      
      <div className="page-actions">
        <button type="button" className="button">
          Back
        </button>
        <button 
          type="button" 
          className="button button-primary button-large"
          onClick={handleSubmit}
        >
          Continue
        </button>
      </div>
    </div>
  );
};

export default ModuleSelection;