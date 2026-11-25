/**
 * Shapes returned by the `erp/v2/hrm/mcp/*` admin API
 * (`WeDevs\ERP\HRM\MCP\AdminController`).
 */

export type McpLevel = 'none' | 'read' | 'write' | 'full';
export type McpMode  = 'read' | 'write' | 'delete';
export type McpLevels = Record< string, McpLevel >;

export interface McpGroup {
	readonly slug:        string;
	readonly label:       string;
	readonly description: string;
	readonly pro:         boolean;
	readonly tool_count:  number;
}

export interface McpTool {
	readonly name:        string;
	readonly title:       string;
	readonly description: string;
	readonly group:       string;
	readonly mode:        McpMode;
	readonly pro:         boolean;
}

export interface McpUser {
	readonly id:     number;
	readonly name:   string;
	readonly email:  string;
	readonly avatar: string;
	readonly roles:  readonly string[];
}

export interface McpOverview {
	readonly enabled:      boolean;
	readonly is_admin:     boolean;
	readonly can_connect:  boolean;
	readonly endpoint_url: string;
	readonly groups:       readonly McpGroup[];
	readonly levels:       McpLevels;
	readonly tools:        readonly McpTool[];
}

export interface McpRoleRule {
	readonly slug:   string;
	readonly label:  string;
	readonly levels: McpLevels;
}

export interface McpUserRule {
	readonly user:   McpUser;
	readonly mode:   'custom' | 'blocked';
	readonly levels: McpLevels;
}

export interface McpSettings {
	readonly enabled:            boolean;
	readonly endpoint_url:       string;
	readonly disabled_tools:     readonly string[];
	readonly allowed_origins:    readonly string[];
	readonly log_retention_days: number;
	readonly groups:             readonly McpGroup[];
	readonly roles:              readonly McpRoleRule[];
	readonly users:              readonly McpUserRule[];
	readonly tools:              readonly McpTool[];
	readonly is_pro_active:      boolean;
}

export interface McpToken {
	readonly id:           number;
	readonly label:        string;
	readonly scope:        'full' | 'read';
	readonly user:         McpUser;
	readonly created_at:   string | null;
	readonly last_used_at: string | null;
	readonly revoked_at:   string | null;
	/** The secret. Present only in the response that created the token. */
	readonly token?:       string;
}

export interface McpLogEntry {
	readonly id:         number;
	readonly user:       McpUser;
	readonly tool:       string;
	readonly tool_title: string;
	readonly arguments:  Record< string, unknown > | null;
	readonly status:     'success' | 'error' | 'refused';
	readonly message:    string | null;
	readonly created_at: string | null;
}

/** What the settings screen sends back when access rules change. */
export interface McpAccessPayload {
	readonly roles: Record< string, McpLevels >;
	readonly users: Record< string, { mode: 'custom' | 'blocked'; groups: McpLevels } >;
}
