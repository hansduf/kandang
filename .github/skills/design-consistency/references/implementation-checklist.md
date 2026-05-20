# Design Implementation Checklist

Use this checklist before submitting code for review. Each section should have all items checked ✓.

## Pre-Implementation (Discovery Phase)

- [ ] Examined existing similar pages in `resources/views/`
- [ ] Identified the correct page type pattern (dashboard/form/table/report)
- [ ] Reviewed relevant component references from `resources/views/components/`
- [ ] Checked `tailwind.config.js` to understand color/font configuration
- [ ] Listed the design pattern components needed
- [ ] Verified no custom CSS will be needed (Tailwind only)

## Implementation (Building Phase)

### Layout & Structure
- [ ] Page wrapped in `<x-app-layout>`
- [ ] Header slot filled with correct title format
- [ ] Content container uses `max-w-7xl mx-auto sm:px-6 lg:px-8`
- [ ] Outer div uses `py-12` for top/bottom padding
- [ ] No custom `<style>` tags added
- [ ] No inline `style=""` attributes used

### Typography
- [ ] Page title: `font-semibold text-2xl text-gray-800` or `text-xl`
- [ ] Section headers: `font-semibold text-lg text-gray-800`
- [ ] Card titles: `font-semibold text-base text-gray-900`
- [ ] Body text: `text-base text-gray-700`
- [ ] Labels: `text-sm font-medium text-gray-700`
- [ ] Helper text: `text-xs text-gray-500`
- [ ] All other text follows typography table (no random sizes)

### Colors
- [ ] No hex colors (#FFFFFF, #000000, etc.) in markup
- [ ] All colors from approved palette (blue-900, gray-100, gray-800, etc.)
- [ ] Hover states use correct color (bg-blue-700, bg-gray-300, etc.)
- [ ] Focus states applied to interactive elements (`focus:ring-2 focus:ring-blue-500`)
- [ ] Text contrast checked (WCAG AA minimum)

### Spacing
- [ ] Section spacing: `mb-6` or `mb-8` between sections
- [ ] Card padding: `p-6` inside cards
- [ ] Form groups: `space-y-4` or `space-y-6`
- [ ] Grid gaps: `gap-4` or `gap-6` (no random values)
- [ ] No spacing values like `px-7`, `py-13`, `mb-10` (use Tailwind scale only)
- [ ] Margins/padding all from `2, 3, 4, 6, 8, 12` scale

### Components
- [ ] Checked if component reuse opportunity exists in `resources/views/components/`
- [ ] Using `<x-alert>`, `<x-sidebar>`, `<x-navbar>` instead of recreating
- [ ] If creating new component, saved in `resources/views/components/`
- [ ] New components documented in SKILL.md [Component Reuse section](#5-component-reuse)

### Forms
- [ ] Using Tailwind Forms plugin for inputs
- [ ] Form groups follow pattern: label → input → error message
- [ ] Input classes: `mt-1 block w-full rounded-md border-gray-300`
- [ ] Labels use: `block text-sm font-medium text-gray-700`
- [ ] Error messages: `mt-1 text-sm text-red-600`
- [ ] Submit button styled: `px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700`

### Tables
- [ ] Using semantic `<table>` with `<thead>`, `<tbody>`, `<tfoot>`
- [ ] Header row: `bg-gray-100 border-b border-gray-200`
- [ ] Header cells: `text-sm font-semibold text-gray-900`
- [ ] Body rows: `border-b border-gray-200 hover:bg-gray-50`
- [ ] Table data: `text-sm text-gray-700`
- [ ] Padding: `px-6 py-3` (header), `px-6 py-4` (body)

### Responsive Design
- [ ] Mobile view tested (single column, no overflow)
- [ ] Tablet view tested (transitional layout)
- [ ] Desktop view tested (full width, multi-column)
- [ ] Using Tailwind breakpoints: `sm:`, `md:`, `lg:`
- [ ] All interactive elements work on touch devices
- [ ] Sidebar collapse works on small screens

### Accessibility
- [ ] Semantic HTML used (`<h1>`, `<h2>`, `<label>`, etc.)
- [ ] Form inputs have associated `<label>` elements
- [ ] Image alt text present (if applicable)
- [ ] Interactive elements keyboard accessible
- [ ] Color contrast meets WCAG AA (4.5:1 for text)
- [ ] Focus indicators visible on all focusable elements

## Review (Pre-Commit Phase)

### Visual Consistency
- [ ] Page matches existing similar pages in structure
- [ ] Headers aligned consistently
- [ ] Card layouts identical to other cards
- [ ] Button styles match other buttons
- [ ] No jarring color differences
- [ ] Spacing matches pattern examples
- [ ] Font sizes match typography table

### Code Quality
- [ ] Ran `grep -r 'style="' resources/views/*.blade.php` → Found only existing (no new)
- [ ] Ran `grep -r '#[0-9A-F]{6}' resources/views/*.blade.php` → No hex colors
- [ ] Ran `grep -r 'px-[79]' resources/views/*.blade.php` → No odd spacing
- [ ] No TODO comments left in code
- [ ] No commented-out code blocks
- [ ] Component names follow `kebab-case`

### Documentation
- [ ] If new component created, added to [Component Reuse section](../../SKILL.md#5-component-reuse)
- [ ] If new pattern added, updated [Common Patterns section](../../SKILL.md#common-patterns-by-page-type)
- [ ] If new color added, updated [Color Scheme section](../../SKILL.md#2-color-scheme)
- [ ] Added meaningful comments for complex logic (not style-related)

### Testing
- [ ] Page loads without console errors
- [ ] All interactive elements respond to clicks/keyboard
- [ ] Forms validate and show error messages correctly
- [ ] Tables scroll horizontally on narrow screens
- [ ] Dropdowns/modals display correctly
- [ ] Navigation links work
- [ ] External links open in new tab if specified

## Final Sign-Off

- [ ] **Developer:** All checklist items verified ✓
- [ ] **Reviewer:** Visually compared with existing pages ✓
- [ ] **Ready to merge:** Design consistency confirmed ✓

---

### Common Issues Found on Review

If any of these appear in your code, fix before committing:

1. **Custom style tags** → Remove, use Tailwind classes
2. **Hex colors** → Replace with Tailwind color classes
3. **Random padding/margin** → Use Tailwind scale (2,3,4,6,8,12)
4. **Duplicate components** → Replace with reusable component reference
5. **Font size inconsistency** → Use text-sm, text-base, text-lg only
6. **Spacing misalignment** → Check similar pages and match exactly
7. **Missing hover/focus states** → Add `hover:` and `focus:` utilities
8. **Poor contrast** → Lighten text or darken background for 4.5:1 ratio
9. **Unresponsive layout** → Add Tailwind breakpoints (sm:, md:, lg:)
10. **Semantic HTML missing** → Use `<label>`, `<h1>`, `<button>` tags correctly
