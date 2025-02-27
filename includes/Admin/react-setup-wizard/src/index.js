import React from '@wordpress/element';
import { render } from '@wordpress/element';
import SetupWizard from './components/SetupWizard';

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

const SettingsPage = () => {
    return <div>Placeholder for settings page</div>;
};

domReady( () => {
    const root = createRoot(
        document.getElementById( 'unadorned-announcement-bar-settings' )
    );

    root.render( <SettingsPage /> );
} );


// const setupWizardRoot = document.getElementById('erp-setup-wizard-root');

// if (setupWizardRoot) {
//     render(<SetupWizard />, setupWizardRoot);
// }