
import React from 'react';
import { HashRouter } from 'react-router-dom';
import App from './components/App';

export default function AppComponenet() {
    return (
        <HashRouter>
            <div className="erp-onboarding-container">
                <App />
            </div>
        </HashRouter>
    );
}