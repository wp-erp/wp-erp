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
	/** Raw post content — the editor binds to this. */
	readonly content:      string;
	/** Server-side `wp_kses_post( wpautop() )` output — the only string the view dialog renders as HTML. */
	readonly html_content: string;
	readonly type:       string;
	/** Delivery channels the legacy metabox owns; both default off. */
	/** Post date, `Y-m-d H:i:s` site time — a future value means Scheduled. */
	readonly publish_date: string;
	readonly send_push:    boolean;
	readonly send_sms:     boolean;
	readonly sms_content:  string;
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
	readonly send_push?:    boolean;
	readonly send_sms?:     boolean;
	readonly sms_content?:  string;
	readonly publish_date?: string;
}

export interface AnnouncementStatusCounts {
	readonly publish: number;
	readonly draft:   number;
	readonly future:  number;
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
	/** Which delivery channels this install has — SMS is a pro module. */
	readonly channels:     { readonly push: boolean; readonly sms: boolean };
}
