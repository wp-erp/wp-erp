import React, { useState } from 'react';

/**
 * UserSetup Page Component
 * 
 * Allows setting up initial users and assigning roles
 */
const UserSetup = ({ onComplete }) => {
  // State for users
  const [users, setUsers] = useState([
    { id: 1, name: '', email: '', role: 'manager', department: '' }
  ]);
  
  // State for form validation
  const [errors, setErrors] = useState({});
  
  // Available roles
  const roles = [
    { id: 'admin', name: 'Administrator', description: 'Full access to all features and settings' },
    { id: 'manager', name: 'Manager', description: 'Can manage most features but cannot modify system settings' },
    { id: 'employee', name: 'Employee', description: 'Basic access to relevant modules and personal data' },
    { id: 'accountant', name: 'Accountant', description: 'Access to financial modules and reports' },
    { id: 'hr', name: 'HR Manager', description: 'Access to HR module and employee records' }
  ];
  
  // Departments
  const departments = [
    { id: 'executive', name: 'Executive' },
    { id: 'finance', name: 'Finance' },
    { id: 'hr', name: 'Human Resources' },
    { id: 'it', name: 'IT' },
    { id: 'marketing', name: 'Marketing' },
    { id: 'operations', name: 'Operations' },
    { id: 'sales', name: 'Sales' }
  ];
  
  // Add a new user row
  const addUser = () => {
    setUsers([
      ...users,
      { id: Date.now(), name: '', email: '', role: 'employee', department: '' }
    ]);
  };
  
  // Remove a user row
  const removeUser = (userId) => {
    if (users.length > 1) {
      setUsers(users.filter(user => user.id !== userId));
    }
  };
  
  // Handle input changes
  const handleChange = (userId, field, value) => {
    setUsers(users.map(user => {
      if (user.id === userId) {
        return { ...user, [field]: value };
      }
      return user;
    }));
    
    // Clear error when field is modified
    if (errors[`${userId}-${field}`]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[`${userId}-${field}`];
        return newErrors;
      });
    }
  };
  
  // Validate the form
  const validateForm = () => {
    const newErrors = {};
    
    users.forEach(user => {
      // Check required fields
      if (!user.name.trim()) {
        newErrors[`${user.id}-name`] = 'Name is required';
      }
      
      if (!user.email.trim()) {
        newErrors[`${user.id}-email`] = 'Email is required';
      } else if (!/^\S+@\S+\.\S+$/.test(user.email)) {
        newErrors[`${user.id}-email`] = 'Please enter a valid email address';
      }
    });
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };
  
  // Handle form submission
  const handleSubmit = () => {
    if (validateForm()) {
      // In a real implementation, save the users data to your state management
      console.log('User setup data:', users);
      
      // Move to next step
      onComplete();
    }
  };
  
  return (
    <div className="erp-onboarding-page user-setup-page">
      <div className="page-header">
        <h1>User Setup</h1>
        <p className="subtitle">Add team members and assign roles</p>
      </div>
      
      <div className="page-content">
        <div className="user-setup-info">
          <p>
            Add the initial users who will access your ERP system. You can add more users
            and modify permissions after setup is complete.
          </p>
        </div>
        
        <div className="users-table-container">
          <table className="users-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Department</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {users.map(user => (
                <tr key={user.id}>
                  <td>
                    <input
                      type="text"
                      value={user.name}
                      onChange={(e) => handleChange(user.id, 'name', e.target.value)}
                      placeholder="Full Name"
                      className={errors[`${user.id}-name`] ? 'error' : ''}
                    />
                    {errors[`${user.id}-name`] && (
                      <div className="error-message">{errors[`${user.id}-name`]}</div>
                    )}
                  </td>
                  <td>
                    <input
                      type="email"
                      value={user.email}
                      onChange={(e) => handleChange(user.id, 'email', e.target.value)}
                      placeholder="email@example.com"
                      className={errors[`${user.id}-email`] ? 'error' : ''}
                    />
                    {errors[`${user.id}-email`] && (
                      <div className="error-message">{errors[`${user.id}-email`]}</div>
                    )}
                  </td>
                  <td>
                    <select
                      value={user.role}
                      onChange={(e) => handleChange(user.id, 'role', e.target.value)}
                    >
                      {roles.map(role => (
                        <option key={role.id} value={role.id}>
                          {role.name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td>
                    <select
                      value={user.department}
                      onChange={(e) => handleChange(user.id, 'department', e.target.value)}
                    >
                      <option value="">Select Department</option>
                      {departments.map(dept => (
                        <option key={dept.id} value={dept.id}>
                          {dept.name}
                        </option>
                      ))}
                    </select>
                  </td>
                  <td>
                    <button
                      type="button"
                      className="button remove-user"
                      onClick={() => removeUser(user.id)}
                      disabled={users.length === 1}
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
          
          <div className="add-user-container">
            <button
              type="button"
              className="button add-user"
              onClick={addUser}
            >
              + Add Another User
            </button>
          </div>
        </div>
        
        <div className="roles-info">
          <h3>About User Roles</h3>
          <div className="roles-grid">
            {roles.map(role => (
              <div key={role.id} className="role-card">
                <h4>{role.name}</h4>
                <p>{role.description}</p>
              </div>
            ))}
          </div>
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

export default UserSetup;