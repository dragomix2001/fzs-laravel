# Frontend Architecture

## CSS Framework Strategy

This project uses **Tailwind CSS 4 as the active utility layer**, with jQuery/Alpine.js for existing interactions and selected legacy Bootstrap styles.

### Tailwind CSS 4 (Active UI Layer)
- **Usage**: Current layouts, forms, cards, tables, alerts and responsive navigation
- **Components**: Blade components, utility classes and Alpine.js interactions
- **Purpose**: Current default for new and recently refactored UI

### Legacy Bootstrap Styles
- **Usage**: Older templates and imported compatibility styles
- **Purpose**: Kept temporarily to avoid visual regressions during incremental migration

## Why Both?

**Migration Cost vs. Value**: Full migration from Bootstrap to Tailwind (or vice versa) would require:
- Rewriting 1900+ Bootstrap class usages
- Regression testing all UI components
- High risk of visual bugs
- Estimated effort: 2-3 weeks

**Current Strategy**: Prefer Tailwind for new work, preserve legacy styles where
rewriting would create visual or workflow risk, and keep component boundaries
clear when legacy classes remain.

## Build Configuration

**Vite Config** (`vite.config.js`):
```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

**Dependencies** (`package.json`):
- `tailwindcss`: ^4.3.1
- `@tailwindcss/vite`: ^4.3.1
- Bootstrap 5: Served from `/public/css/bootstrap.min.css`

## Performance Considerations

- **Total CSS size**: ~145KB (Bootstrap) + ~15KB (Tailwind utilities) = 160KB
- **Gzip compression**: Reduces to ~25KB
- **Acceptable overhead**: Both frameworks are minified and cached

## Future Direction

When considering full migration:
1. **Bootstrap → Tailwind**: If rapid prototyping and utility-first approach becomes priority
2. **Tailwind → Bootstrap**: If component consistency and rapid UI development is priority
3. **Keep both**: If development velocity and stability are balanced priorities (current choice)

## Developer Guidelines

### For New Features
- **Dashboard, Forms, Tables** → Tailwind Blade components
- **Interactive components, Animations** → Tailwind + Alpine.js/jQuery
- **API responses, JSON views** → Unstyled or minimal Tailwind

### Component Checklist
Before adding a new component, ask:
- [ ] Does Bootstrap have this component already?
- [ ] Do I need fine-grained control? (Tailwind)
- [ ] Is this a one-off custom design? (Tailwind)
- [ ] Should this match existing UI? (Bootstrap)

---

**Last Updated**: September 6, 2026
**Status**: Tailwind-first incremental frontend migration
