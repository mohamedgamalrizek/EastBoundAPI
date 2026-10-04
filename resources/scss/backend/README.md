# Backend SCSS

Backend styles are maintained exclusively in this directory. Files under
`public/backend/css` are compiled output, except for the protected
`custom.css` and Flag Icon stylesheets.

- `abstracts/` contains variables, theme primitives, and reusable mixins.
- `base/` contains imports, resets, and application foundations.
- `vendors/` contains the recovered Bootstrap-compatible framework layer.
- `utilities/` contains gradients, spacing, grid, and helper utilities.
- `layouts/` contains the shell, header/footer, navigation, sidebar, and RTL.
- `components/` contains forms, tables, cards, charts, email, and UI controls.
- `pages/` contains authentication, profile, pricing, and legacy page rules.
- `themes/` contains dark mode and legacy theme variants.
- `foundation.scss`, `responsive.scss`, and `admin.scss` are build entries.

The three outputs intentionally preserve the existing cascade around
`public/backend/css/custom.css`, which is protected and is not generated:

1. `backend-foundation.css`
2. `custom.css`
3. `backend-responsive.css`
4. plugin styles
5. `flow-admin.css`

Run `npm run build:backend-css` after editing SCSS. Theme-related values belong
in `abstracts/_theme.scss`; component partials consume custom properties.
Shared compile-time values and behavior belong in `_variables.scss` and
`_mixins.scss`. Dark mode is token-only and is enabled with
`data-theme="dark"` on `.tv-backend`.
