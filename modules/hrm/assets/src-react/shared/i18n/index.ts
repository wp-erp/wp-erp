/**
 * i18n facade — re-exports of `@wordpress/i18n` primitives.
 *
 * Every translation call inside the shell flows through this module so the
 * `'erp'` text domain is implicit. ESLint's `i18n-text-domain` rule still
 * accepts the explicit form `__('foo', 'erp')` — both work.
 */

export { __, _x, _n, _nx, sprintf } from '@wordpress/i18n';
export { dateI18n } from '@wordpress/date';

export { I18N_DOMAIN } from '@/shared/filters';
