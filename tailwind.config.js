const plugin = require('tailwindcss/plugin');

// Root class for scoping - will be used to isolate Tailwind styles
const rootClass = '#wperp-onboarding-root';

/** @type {import('tailwindcss').Config} */
module.exports = {
  important: rootClass,
  purge: {
    enabled: process.env.WEBPACK_ENV === 'production',
    content: [
      './includes/Admin/Onboarding/assets/src/**/*.{js,jsx,ts,tsx}',
    ],
  },
  theme: {
    extend: {
      colors: {
        primary: {
          600: '#2563eb',
        },
        slate: {
          500: '#64748b',
        },
      },
      fontSize: {
        '15px': '15px',
        '30px': '30px',
      },
      maxWidth: {
        '150px': '150px',
        '640px': '640px',
      },
      width: {
        '640px': '640px',
      },
      spacing: {
        '50px': '50px',
        '85px': '85px',
        '100px': '100px',
        '138.8px': '138.8px',
      },
      maxHeight: {
        '340px': '340px',
      },
    },
  },
  corePlugins: {
    preflight: false,
  },
  plugins: [
    // Custom scoped preflight plugin
    plugin(function({ addBase }) {
      addBase({
        [`${rootClass}`]: {
          'box-sizing': 'border-box',
          'border-width': '0',
          'border-style': 'solid',
          'border-color': 'currentColor',
          'font-family': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
          '-webkit-font-smoothing': 'antialiased',
          '-moz-osx-font-smoothing': 'grayscale',
        },
        [`${rootClass} *, ${rootClass} ::before, ${rootClass} ::after`]: {
          'box-sizing': 'border-box',
          'border-width': '0',
          'border-style': 'solid',
          'border-color': 'currentColor',
        },
        [`${rootClass} hr`]: {
          'height': '0',
          'color': 'inherit',
          'border-top-width': '1px',
        },
        [`${rootClass} abbr:where([title])`]: {
          'text-decoration': 'underline dotted',
        },
        [`${rootClass} h1, ${rootClass} h2, ${rootClass} h3, ${rootClass} h4, ${rootClass} h5, ${rootClass} h6`]: {
          'font-size': 'inherit',
          'font-weight': 'inherit',
        },
        [`${rootClass} a`]: {
          'color': 'inherit',
          'text-decoration': 'inherit',
        },
        [`${rootClass} b, ${rootClass} strong`]: {
          'font-weight': 'bolder',
        },
        [`${rootClass} code, ${rootClass} kbd, ${rootClass} samp, ${rootClass} pre`]: {
          'font-family': 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
          'font-size': '1em',
        },
        [`${rootClass} small`]: {
          'font-size': '80%',
        },
        [`${rootClass} sub, ${rootClass} sup`]: {
          'font-size': '75%',
          'line-height': '0',
          'position': 'relative',
          'vertical-align': 'baseline',
        },
        [`${rootClass} sub`]: {
          'bottom': '-0.25em',
        },
        [`${rootClass} sup`]: {
          'top': '-0.5em',
        },
        [`${rootClass} table`]: {
          'text-indent': '0',
          'border-color': 'inherit',
          'border-collapse': 'collapse',
        },
        [`${rootClass} button, ${rootClass} input, ${rootClass} optgroup, ${rootClass} select, ${rootClass} textarea`]: {
          'font-family': 'inherit',
          'line-height': 'inherit',
        },
        [`${rootClass} button, ${rootClass} select`]: {
          'text-transform': 'none',
        },
        [`${rootClass} button, ${rootClass} [type='button'], ${rootClass} [type='reset'], ${rootClass} [type='submit']`]: {
          '-webkit-appearance': 'button',
          'background-color': 'transparent',
          'background-image': 'none',
        },
        [`${rootClass} :-moz-focusring`]: {
          'outline': 'auto',
        },
        [`${rootClass} :-moz-ui-invalid`]: {
          'box-shadow': 'none',
        },
        [`${rootClass} progress`]: {
          'vertical-align': 'baseline',
        },
        [`${rootClass} ::-webkit-inner-spin-button, ${rootClass} ::-webkit-outer-spin-button`]: {
          'height': 'auto',
        },
        [`${rootClass} [type='search']`]: {
          '-webkit-appearance': 'textfield',
          'outline-offset': '-2px',
        },
        [`${rootClass} ::-webkit-search-decoration`]: {
          '-webkit-appearance': 'none',
        },
        [`${rootClass} ::-webkit-file-upload-button`]: {
          '-webkit-appearance': 'button',
          'font': 'inherit',
        },
        [`${rootClass} summary`]: {
          'display': 'list-item',
        },
        [`${rootClass} blockquote, ${rootClass} dl, ${rootClass} dd, ${rootClass} h1, ${rootClass} h2, ${rootClass} h3, ${rootClass} h4, ${rootClass} h5, ${rootClass} h6, ${rootClass} hr, ${rootClass} figure, ${rootClass} p, ${rootClass} pre`]: {
          'margin': '0',
        },
        [`${rootClass} fieldset`]: {
          'margin': '0',
          'padding': '0',
        },
        [`${rootClass} legend`]: {
          'padding': '0',
        },
        [`${rootClass} ol, ${rootClass} ul, ${rootClass} menu`]: {
          'list-style': 'none',
          'margin': '0',
          'padding': '0',
        },
        [`${rootClass} textarea`]: {
          'resize': 'vertical',
        },
        [`${rootClass} input::placeholder, ${rootClass} textarea::placeholder`]: {
          'opacity': '1',
          'color': '#9ca3af',
        },
        [`${rootClass} button, ${rootClass} [role="button"]`]: {
          'cursor': 'pointer',
        },
        [`${rootClass} :disabled`]: {
          'cursor': 'default',
        },
        [`${rootClass} img, ${rootClass} svg, ${rootClass} video, ${rootClass} canvas, ${rootClass} audio, ${rootClass} iframe, ${rootClass} embed, ${rootClass} object`]: {
          'display': 'block',
          'vertical-align': 'middle',
        },
        [`${rootClass} img, ${rootClass} video`]: {
          'max-width': '100%',
          'height': 'auto',
        },
        [`${rootClass} [hidden]`]: {
          'display': 'none',
        },
      });
    }),
  ],
}
