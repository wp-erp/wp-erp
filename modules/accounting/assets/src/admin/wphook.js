import { createHooks } from '@wordpress/hooks';

window.acct = {};
acct.hooks  = createHooks();

acct.addFilter = (hookName, namespace, component, priority = 10) => {
    acct.hooks.addFilter(hookName, namespace, (components) => {
        components.push(component);
        return components;
    }, priority );
};
