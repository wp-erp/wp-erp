import { expect as baseExpect, type APIResponse } from '@playwright/test';

/**
 * Custom expect matchers registered in the configs via `expect.extend(customExpect)`.
 */
export const customExpect = {
    toBeOkApi(response: APIResponse) {
        const pass = response.ok();
        return {
            pass,
            message: () => `expected API response to be ok (2xx), got ${response.status()} ${response.statusText()} for ${response.url()}`,
        };
    },

    toHaveStatusCode(response: APIResponse, expected: number) {
        const actual = response.status();
        return {
            pass: actual === expected,
            message: () => `expected status ${expected}, got ${actual} for ${response.url()}`,
        };
    },
};

export const expect = baseExpect.extend(customExpect);
