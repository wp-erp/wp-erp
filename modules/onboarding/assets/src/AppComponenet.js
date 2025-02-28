
import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import App from './components/App';

export default function AppComponenet() {
    return (
        <BrowserRouter>
            <div>
                <h1>Hello Onboarding</h1>
                <App />
            </div>
        </BrowserRouter>
    );
}