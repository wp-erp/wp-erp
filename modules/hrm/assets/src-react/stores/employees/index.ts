/**
 * `erp-hr/employees` @wordpress/data store registration.
 */

import { createReduxStore, register } from '@wordpress/data';

import * as actions   from './actions';
import reducer        from './reducer';
import * as resolvers from './resolvers';
import * as selectors from './selectors';
import { STORE_NAME } from './types';

export const storeName = STORE_NAME;

const config = {
	reducer,
	actions,
	selectors,
	resolvers,
} as const;

export const employeesStore = createReduxStore( storeName, config );

register( employeesStore );

export type {
	BulkActionsFilter,
	ColumnContext,
	ColumnsFilter,
	EmployeeBulkAction,
	EmployeeColumn,
	EmployeeCountsQuery,
	EmployeeFilter,
	EmployeeListError,
	EmployeeListItem,
	EmployeeListMeta,
	EmployeeListQuery,
	EmployeeLookup,
	EmployeeReportingTo,
	EmployeeRowAction,
	EmployeeStatus,
	EmployeeStatusCounts,
	EmployeeToolbarItem,
	EmployeesState,
	FiltersFilter,
	RowActionsFilter,
	ToolbarItemsFilter,
} from './types';

export type { EmployeeCreateInput, EmployeeTerminateInput } from './actions';

export { toCountsKey, toCountsQuery, toQueryKey } from './query-key';
export {
	bulkDeleteEmployees,
	bulkRestoreEmployees,
	createEmployee,
	deleteEmployee,
	fetchCounts,
	fetchEmployees,
	fetchEmployeeForEdit,
	reactivateEmployee,
	restoreEmployee,
	terminateEmployee,
	updateEmployee,
	invalidate,
	setCounts,
	setEmployees,
	setError,
	setFilters,
	setLoading,
	setPagination,
	setSelectedIds,
	setSort,
} from './actions';
export {
	getCounts,
	getCurrentQuery,
	getEmployees,
	getEmployeeById,
	getError,
	getFilters,
	getMeta,
	getPagination,
	getSelectedIds,
	getSort,
	getTotal,
	getTotalPages,
	isLoading,
} from './selectors';
