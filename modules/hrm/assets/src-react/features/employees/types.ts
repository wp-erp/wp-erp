/**
 * Public types re-exported for the Employees feature + pro consumers.
 *
 * Pro plugins consume these via the `.d.ts` bundle emitted by
 * `npm run build:types` (modules/hrm/assets/dist-react/types/).
 */

export type {
	BulkActionsFilter,
	ColumnContext,
	ColumnsFilter,
	EmployeeBulkAction,
	EmployeeColumn,
	EmployeeFilter,
	EmployeeListError,
	EmployeeListItem,
	EmployeeListMeta,
	EmployeeListQuery,
	EmployeeLookup,
	EmployeeReportingTo,
	EmployeeRowAction,
	EmployeeStatus,
	EmployeeToolbarItem,
	FiltersFilter,
	RowActionsFilter,
	ToolbarItemsFilter,
} from '@/stores/employees';

export { EMPLOYEES_HOOKS, EMPLOYEES_ACTIONS, COLUMN_IDS } from './constants';
