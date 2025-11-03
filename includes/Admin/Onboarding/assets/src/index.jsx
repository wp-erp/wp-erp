import { createRoot } from 'react-dom/client';
import App from './App';
import './styles/index.css';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  const rootElement = document.getElementById('wperp-onboarding-root');

  if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<App />);
  } else {
    console.error('WP ERP Onboarding: Root element not found');
  }
});
