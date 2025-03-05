
import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import App from './components/App';

export default function AppComponenet() {
    return (
        <BrowserRouter basename="/">
            <div>
                <App />
            </div>
        </BrowserRouter>
    );
}