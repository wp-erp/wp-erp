/**
 * Standalone "HR vendor globals" entry.
 *
 * Exposes the free app's shared singletons on `window` WITHOUT mounting any app.
 * It exists so the pro STANDALONE admin pages (Workflow, Custom Field Builder) —
 * which render on their own `?page=` slug where the free HR app is never loaded —
 * can still externalize `@wedevs/plugin-ui` + `react-router-dom` against these
 * globals instead of each bundling their own ~3.9MB copy. Those pages enqueue
 * free's `vendor.js` + this `hr-globals.js` (as ordered script dependencies)
 * before their own bundle.
 *
 * Mirrors the exposure block in `employees.entry.tsx` (same instances, same
 * mechanism) — keep them in sync.
 */

import * as ReactRouterDOM from 'react-router-dom';
import * as ERPPluginUI from '@wedevs/plugin-ui';

( window as unknown as { ReactRouterDOM?: typeof ReactRouterDOM } ).ReactRouterDOM = ReactRouterDOM;
( window as unknown as { ERPPluginUI?: typeof ERPPluginUI } ).ERPPluginUI = ERPPluginUI;
