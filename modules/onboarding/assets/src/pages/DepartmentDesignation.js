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
      const response = await axios.get(`${ErpOnboard.restUrl}erp/v1/onboarding/departments`, {
        headers: {
          'X-WP-Nonce': ErpOnboard.nonce
        }
      });
      setDepartments(response.data);
    } catch (err) {
      console.error('Error fetching departments:', err);
    }
  };

  const fetchDesignations = async () => {
    try {
      const response = await axios.get(`${ErpOnboard.restUrl}erp/v1/onboarding/designations`, {
        headers: {
          'X-WP-Nonce': ErpOnboard.nonce
        }
      });
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
        await axios.post(`${ErpOnboard.restUrl}erp/v1/onboarding/departments`, { name: newItem }, {
          headers: {
            'X-WP-Nonce': ErpOnboard.nonce
          }
        });
        await fetchDepartments();
      } else {
        await axios.post(`${ErpOnboard.restUrl}erp/v1/onboarding/designations`, { name: newItem }, {
          headers: {
            'X-WP-Nonce': ErpOnboard.nonce
          }
        });
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
        await axios.delete(`${ErpOnboard.restUrl}erp/v1/onboarding/departments/${id}`, {
          headers: {
            'X-WP-Nonce': ErpOnboard.nonce
          }
        });
        await fetchDepartments();
      } else {
        await axios.delete(`${ErpOnboard.restUrl}erp/v1/onboarding/designations/${id}`, {
          headers: {
            'X-WP-Nonce': ErpOnboard.nonce
          }
        });
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
            <div className="icon">👥</div>
            <span>Departments</span>
          </button>
          <button
            className={`tab ${activeTab === 'designations' ? 'active' : ''}`}
            onClick={() => setActiveTab('designations')}
          >
            <div className="icon">💼</div>
            <span>Designations</span>
          </button>
        </div>

        <style jsx="true">{`
          .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
          }

          .tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 200px;
            height: 150px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
          }

          .tab:hover {
            border-color: #2271b1;
            background: #f0f6fc;
          }

          .tab.active {
            border-color: #2271b1;
            background: #f0f6fc;
          }

          .tab .icon {
            font-size: 24px;
            margin-bottom: 5px;
          }

          .tab span {
            font-size: 16px;
            font-weight: 500;
            color: #1d2327;
          }
        `}</style>
        <div className="content-area">
          <div className="add-new">
            <div className="department-select">
              <div className="select-input">
                <input
                  type="text"
                  value={newItem}
                  onChange={(e) => setNewItem(e.target.value)}
                  placeholder={`Add new ${activeTab.slice(0, -1)}`}
                  className="select-search"
                />
                <div className="selected-items">
                  {(activeTab === 'departments' ? departments : designations).map(item => (
                    <div key={item.id} className="selected-tag">
                      <span>{item.title || item.name}</span>
                      <button
                        onClick={() => handleDelete(item.id)}
                        className="tag-remove"
                        disabled={loading}
                      >
                        ×
                      </button>
                    </div>
                  ))}
                </div>
              </div>
              <button
                onClick={handleAdd}
                disabled={loading || !newItem.trim()}
                className="button button-primary add-button"
              >
                Add
              </button>
            </div>
          </div>

          {error && <div className="error-message">{error}</div>}

          <style jsx="true">{`
            .department-select {
              position: relative;
              width: 100%;
            }

            .select-input {
              border: 1px solid #ddd;
              border-radius: 4px;
              padding: 8px;
              background: white;
              min-height: 42px;
            }

            .select-search {
              border: none;
              outline: none;
              width: 100%;
              padding: 4px;
              margin-bottom: 8px;
            }

            .selected-items {
              display: flex;
              flex-wrap: wrap;
              gap: 8px;
            }

            .selected-tag {
              display: flex;
              align-items: center;
              background: #e8f0fe;
              border-radius: 16px;
              padding: 4px 12px;
              font-size: 14px;
              color: #1a73e8;
            }

            .tag-remove {
              background: none;
              border: none;
              color: #5f6368;
              margin-left: 8px;
              cursor: pointer;
              padding: 0 4px;
              font-size: 16px;
            }

            .tag-remove:hover {
              color: #d93025;
            }

            .add-button {
              margin-top: 12px;
            }
          `}</style>
        </div>

        <div className="page-actions">
          <button
            onClick={() => navigate('/leave')}
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
