import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const DepartmentDesignation = ({ onComplete }) => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('departments');
  const [departments, setDepartments] = useState([]);
  const [designations, setDesignations] = useState([]);
  const [newItem, setNewItem] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchDepartments();
    fetchDesignations();
  }, []);

  const fetchDepartments = async () => {
    try {
      const response = await axios.get('/wp-json/erp/v1/onboarding/departments');
      setDepartments(response.data);
    } catch (err) {
      console.error('Error fetching departments:', err);
    }
  };

  const fetchDesignations = async () => {
    try {
      const response = await axios.get('/wp-json/erp/v1/onboarding/designations');
      setDesignations(response.data);
    } catch (err) {
      console.error('Error fetching designations:', err);
    }
  };

  const handleAdd = async () => {
    if (!newItem.trim()) return;
    setLoading(true);
    setError(null);

    try {
      if (activeTab === 'departments') {
        await axios.post('/wp-json/erp/v1/onboarding/departments', { name: newItem });
        await fetchDepartments();
      } else {
        await axios.post('/wp-json/erp/v1/onboarding/designations', { name: newItem });
        await fetchDesignations();
      }
      setNewItem('');
    } catch (err) {
      setError(`Failed to add ${activeTab.slice(0, -1)}. Please try again.`);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    setLoading(true);
    try {
      if (activeTab === 'departments') {
        await axios.delete(`/wp-json/erp/v1/onboarding/departments/${id}`);
        await fetchDepartments();
      } else {
        await axios.delete(`/wp-json/erp/v1/onboarding/designations/${id}`);
        await fetchDesignations();
      }
    } catch (err) {
      setError(`Failed to delete ${activeTab.slice(0, -1)}. Please try again.`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="erp-onboarding-page department-designation">
      <div className="page-header">
        <span className="step-count">2/5</span>
        <h2>Department and Designation</h2>
      </div>

      <div className="page-content">
        <div className="section-header">
          <h1>Make Your Department and Designation</h1>
          <p>Enter you company name and start date.</p>
        </div>

        <div className="tabs">
          <button 
            className={`tab ${activeTab === 'departments' ? 'active' : ''}`}
            onClick={() => setActiveTab('departments')}
          >
            <span className="icon">👥</span>
            Departments
          </button>
          <button 
            className={`tab ${activeTab === 'designations' ? 'active' : ''}`}
            onClick={() => setActiveTab('designations')}
          >
            <span className="icon">💼</span>
            Designations
          </button>
        </div>

        <div className="content-area">
          <div className="add-new">
            <input
              type="text"
              value={newItem}
              onChange={(e) => setNewItem(e.target.value)}
              placeholder={`Add new ${activeTab.slice(0, -1)}`}
            />
            <button 
              onClick={handleAdd}
              disabled={loading || !newItem.trim()}
              className="button button-primary"
            >
              Add
            </button>
          </div>

          {error && <div className="error-message">{error}</div>}

          <div className="items-list">
            {(activeTab === 'departments' ? departments : designations).map(item => (
              <div key={item.id} className="item">
                <span>{item.title || item.name}</span>
                <button 
                  onClick={() => handleDelete(item.id)}
                  className="delete-button"
                  disabled={loading}
                >
                  ×
                </button>
              </div>
            ))}
          </div>
        </div>

        <div className="page-actions">
          <button 
            onClick={() => navigate('/import-employee')}
            className="button button-primary"
            disabled={loading}
          >
            Next
          </button>
        </div>
      </div>
    </div>
  );
};

export default DepartmentDesignation;
