/**
 * Single import point for every @wordpress/data store the React shell owns.
 *
 * Each child module registers its store as an import side-effect via
 * `register( createReduxStore(...) )`. Pro plugins register their stores from
 * their own entry — they never reach into this barrel.
 */

import './me';
import './employees';

// Future stores plug in here (Departments, Designations, Leave, Announcements,
// Reports, etc.).
