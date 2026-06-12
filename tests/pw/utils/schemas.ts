import { z } from 'zod';

/**
 * Zod schemas to validate WP ERP REST responses in API specs.
 * Loose by design (`.passthrough()`), asserting only the fields the suite relies on.
 */
export const schemas = {
    employee: z
        .object({
            user_id: z.union([z.number(), z.string()]).optional(),
            id: z.union([z.number(), z.string()]).optional(),
            email: z.string().optional(),
            first_name: z.string().optional(),
            last_name: z.string().optional(),
        })
        .passthrough(),

    department: z.object({ id: z.union([z.number(), z.string()]), title: z.string().optional() }).passthrough(),

    designation: z.object({ id: z.union([z.number(), z.string()]), title: z.string().optional() }).passthrough(),

    person: z
        .object({ id: z.union([z.number(), z.string()]), email: z.string().optional(), first_name: z.string().optional() })
        .passthrough(),

    invoice: z.object({ id: z.union([z.number(), z.string()]) }).passthrough(),

    list: <T extends z.ZodTypeAny>(item: T) => z.array(item),
};
