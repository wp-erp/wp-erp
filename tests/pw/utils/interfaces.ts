/** Shapes the suite passes around. Kept narrow on purpose — widen when a spec needs it. */

export interface ErpLicense {
    email: string;
    key: string;
    subscription_type: string;
}

export interface ErpLicenseStatus {
    success: boolean;
    license: string;
    item_id: number;
    item_name: string;
    checksum: string;
    expires: string;
    customer_name: string;
    customer_email: string;
    license_limit: number;
    site_count: number;
    activations_left: number;
    subscription_status: string;
    users: number;
    license_id: number;
    tier: string;
    extensions: string[];
}

export interface ErpUserCount {
    counted_roles: string[];
    count_users: number;
    licensed_user: number;
}

export interface SeededUser {
    id: number;
    login: string;
    email: string;
    role: string;
}

export interface Employee {
    first_name: string;
    last_name: string;
    email: string;
    designation?: number | string;
    department?: number | string;
    location?: number | string;
    hiring_source?: string;
    hiring_date?: string;
    date_of_birth?: string;
    type?: string;
    status?: string;
    pay_rate?: string;
    pay_type?: string;
}

export interface Contact {
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    life_stage?: string;
    contact_owner?: number;
}
