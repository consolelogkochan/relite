import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    { ignores: ['node_modules', 'vendor', 'public', 'bootstrap/ssr', 'storage'] },
    js.configs.recommended,
    tseslint.configs.recommended,
    {
        files: ['resources/js/**/*.{ts,tsx}', 'tests/js/**/*.{ts,tsx}'],
        plugins: { 'react-hooks': reactHooks },
        rules: { ...reactHooks.configs.recommended.rules },
        languageOptions: { globals: { ...globals.browser } },
    },
    prettier,
);
