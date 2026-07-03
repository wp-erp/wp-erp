/**
 * Announcement shapes for the `erp/v2/announcements` endpoints.
 */

import type { AvatarPerson } from '@/shared/components/EmployeeAvatarStack';

/** A list row (`GET /announcements`). */
export interface Announcement {
	readonly id:                 number;
	readonly title:              string;
	readonly excerpt:            string;
	readonly status:             string;
	readonly date:               string | null;
	readonly author:             string;
	readonly recipient_count:    number;
	readonly recipients_preview: readonly AvatarPerson[];
	readonly type:               string;
	readonly type_label:         string;
}

/** Single announcement (`GET /announcements/{id}`) — adds body + recipients. */
export interface AnnouncementDetail extends Announcement {
	readonly content:    string;
	readonly type:       string;
	readonly recipients: {
		readonly employees:    readonly number[];
		readonly departments:  readonly number[];
		readonly designations: readonly number[];
	};
}

export type AnnouncementAssignType =
	| 'all_employee'
	| 'by_department'
	| 'by_designation'
	| 'selected_employee';

/** Create/update payload for `POST|PUT /announcements`. */
export interface AnnouncementInput {
	readonly title:         string;
	readonly content:       string;
	readonly status:        'publish' | 'draft';
	readonly assign_type:   AnnouncementAssignType;
	readonly employees?:    readonly number[];
	readonly departments?:  readonly number[];
	readonly designations?: readonly number[];
}

export interface AnnouncementStatusCounts {
	readonly publish: number;
	readonly draft:   number;
	readonly trash:   number;
}

export interface IdName {
	readonly id:    number;
	readonly name?: string;
	readonly title?: string;
}

export interface AnnouncementFormOptions {
	readonly assignTypes:  ReadonlyArray< { value: string; label: string } >;
	readonly departments:  readonly IdName[];
	readonly designations: readonly IdName[];
	readonly employees:    readonly IdName[];
}
