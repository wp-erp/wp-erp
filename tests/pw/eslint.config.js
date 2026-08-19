const tseslint = require('@typescript-eslint/eslint-plugin');
const tsparser = require('@typescript-eslint/parser');
const playwright = require('eslint-plugin-playwright');
const prettier = require('eslint-config-prettier');

module.exports = [
    {
        ignores: ['node_modules/**', 'playwright-report/**', 'test-results/**', 'blob-report/**', 'harness/**'],
    },
    {
        files: ['**/*.ts'],
        languageOptions: {
            parser: tsparser,
            parserOptions: { ecmaVersion: 2022, sourceType: 'module' },
        },
        plugins: { '@typescript-eslint': tseslint },
        rules: {
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            '@typescript-eslint/no-explicit-any': 'warn',
            'no-console': 'off',
        },
    },
    {
        // Specs hold tests only. Page objects and utils hold everything else.
        files: ['tests/**/*.spec.ts'],
        ...playwright.configs['flat/recommended'],
        rules: {
            ...playwright.configs['flat/recommended'].rules,
            'playwright/no-skipped-test': ['warn', { allowConditional: true }],
            'playwright/expect-expect': 'error',
            'playwright/no-conditional-in-test': 'off',
        },
    },
    prettier,
];
