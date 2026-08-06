import type { APIResponse } from '@playwright/test';

/**
 * Types for the custom matchers registered in utils/pwMatchers.ts and wired into
 * both configs with `expect.extend(customExpect)`. Without this declaration the
 * matchers exist at runtime but are invisible to tsc, so `npm run type:check`
 * cannot catch a misspelled or misapplied one.
 */
declare global {
    namespace PlaywrightTest {
        interface Matchers<R, T> {
            /** Passes when the APIResponse status is 2xx. */
            toBeOkApi(): T extends APIResponse ? R : never;
            /** Passes when the APIResponse status equals `expected`. */
            toHaveStatusCode(expected: number): T extends APIResponse ? R : never;
        }
    }
}

export {};
