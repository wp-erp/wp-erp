import React from '@wordpress/element';
import { render } from '@wordpress/element';
import SetupWizard from './components/SetupWizard';

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow } from '@wordpress/components';

const SettingsPage = () => {
    return (
        <Panel>
            <PanelBody>
                <PanelRow>
                    <div>Placeholder for message control</div>
                </PanelRow>
                <PanelRow>
                    <div>Placeholder for display control</div>
                </PanelRow>
            </PanelBody>
            <PanelBody
                title={ __( 'Appearance', 'unadorned-announcement-bar' ) }
                initialOpen={ false }
            >
                <PanelRow>
                    <div>Placeholder for size control</div>
                </PanelRow>
            </PanelBody>
        </Panel>
   );
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