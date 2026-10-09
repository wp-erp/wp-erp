/**
 * Employee name cell for report tables — thin alias over the shared
 * `PersonCell` (avatar + name), kept so the report pages can import a
 * report-local name without reaching across features.
 */

export { PersonCell as ReportNameCell } from '@/shared/components/PersonCell';
