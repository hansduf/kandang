---
name: design-consistency
description: 'Frontend development guide for maintaining design consistency. Use when: building new pages, refining UI components, styling forms/tables, and ensuring visual coherence. Enforce Tailwind design system, reuse existing components, avoid custom styles, maintain typography/spacing/color standards.'
argument-hint: 'Describe the page/component you are building and what design patterns it needs'
---

# Design System Consistency Guide

**Author Role:** Frontend Developer & UI Designer Expert  
**Primary Goal:** Maintain visual and functional consistency across all pages using the established design system

## When to Use

Invoke this skill:
- **Building new pages** (dashboard, forms, tables, reports)
- **Creating reusable components** (cards, buttons, inputs, alerts)
- **Styling existing features** before deployment
- **Reviewing UI changes** for consistency violations
- **Debugging layout issues** related to spacing/alignment
- **Adding new sections** to existing pages

## ✅ MUST Follow (Non-Negotiable)

### 1. **Use the Existing Design System**
- **Design Framework:** Tailwind CSS (PostCSS)
- **Font:** Figtree (400, 500, 600 weights)
- **Color Palette:** Tailwind defaults + project-specific overrides
- **Layout Engine:** Flex + Grid (Tailwind utilities only)
- **Tools:** Alpine.js for interactivity, Font Awesome 6+ for icons

### 2. **Color Scheme**
Reuse these colors from the design system:

| Element | Color Class | Usage |
|---------|------------|-------|
| **Primary Sidebar** | `bg-blue-900` | Navigation, dark theme |
| **Page Background** | `bg-gray-100`, `bg-gray-50` | Main container |
| **Card/Box Background** | `bg-white` | Content cards, forms |
| **Text (Primary)** | `text-gray-800`, `text-gray-900` | Headers, body text |
| **Text (Secondary)** | `text-gray-600`, `text-gray-700` | Subtle text, labels |
| **Borders** | `border-gray-200`, `border-gray-300` | Dividers, outlines |
| **Shadows** | `shadow-sm`, `shadow-md` | Elevation |
| **Hover/Focus States** | `hover:bg-blue-800`, `focus:ring-blue-500` | Interactive feedback |

**DO NOT create new color values.** If a color isn't in the list, check existing pages first. If truly needed, update `tailwind.config.js` as a team decision.

### 3. **Typography Rules**

| Element | Class Pattern | Example |
|---------|--------------|---------|
| **Page Title** | `font-semibold text-xl` or `text-2xl` | `<h1 class="font-semibold text-2xl text-gray-900">Dashboard</h1>` |
| **Section Headers** | `font-semibold text-lg` | `<h2 class="font-semibold text-lg text-gray-800">Recent Sales</h2>` |
| **Card Title** | `font-semibold text-base` | `<h3 class="font-semibold text-base text-gray-900">Items</h3>` |
| **Body Text** | `text-base text-gray-700` | `<p class="text-base text-gray-700">Content here</p>` |
| **Small Text/Label** | `text-sm text-gray-600` | `<label class="text-sm text-gray-600">Name:</label>` |
| **Extra Small** | `text-xs text-gray-500` | `<span class="text-xs text-gray-500">Helper text</span>` |

**Weights:** Only use `font-normal` (400), `font-semibold` (600), or `font-bold` (700). Never `font-thin` or random weights.

### 4. **Spacing Conventions**

Maintain consistent padding/margin using Tailwind spacing scale:

| Area | Pattern | Example |
|------|---------|---------|
| **Page Container** | `px-4 py-8` or `px-6 py-12` | `<div class="px-4 py-8">` |
| **Card Padding** | `p-6` | `<div class="bg-white p-6 rounded-lg">` |
| **Section Spacing** | `mb-6` or `mb-8` between sections | `<section class="mb-8">` |
| **Form Spacing** | `space-y-4` for vertical form groups | `<form class="space-y-4">` |
| **Grid Gap** | `gap-4` or `gap-6` | `<div class="grid grid-cols-3 gap-4">` |

**Avoid random spacing.** Use: `2, 3, 4, 6, 8, 12` (Tailwind scale). Never `px-7`, `py-13`, etc.

### 5. **Component Reuse**

Before creating a new component, check if it already exists:

| Component | Location | When to Use |
|-----------|----------|------------|
| **Alert** | `resources/views/components/alert.blade.php` | Success, warning, error messages |
| **Sidebar** | `resources/views/components/sidebar.blade.php` | Navigation structure |
| **Navbar** | `resources/views/components/navbar.blade.php` | Top header bar |
| **Button** | Form/link buttons inline | Standard CTA |
| **Form Inputs** | Tailwind Forms plugin | Text, email, number, select, textarea |
| **Card Layout** | `bg-white p-6 rounded-lg shadow-sm` | Content containers |

**If a component doesn't exist**, create it as a reusable Blade component in `resources/views/components/` and document it here.

### 6. **Form Styling**

Use Tailwind Forms plugin for consistency:

```blade
<!-- Text Input -->
<input type="text" class="mt-1 block w-full rounded-md border-gray-300">

<!-- Select Dropdown -->
<select class="mt-1 block w-full rounded-md border-gray-300">
    <option>Option 1</option>
</select>

<!-- Textarea -->
<textarea class="mt-1 block w-full rounded-md border-gray-300"></textarea>

<!-- Checkbox -->
<input type="checkbox" class="rounded border-gray-300">
```

**Form Group Pattern:**
```blade
<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700">Field Name</label>
        <input type="text" class="mt-1 block w-full rounded-md border-gray-300">
    </div>
</div>
```

### 7. **Tables & Lists**

Maintain consistent table styling:

```blade
<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100 border-b border-gray-200">
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900">Header</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700">Data</td>
            </tr>
        </tbody>
    </table>
</div>
```

## ❌ MUST NOT Do (Prohibited)

| ❌ Anti-Pattern | ✅ Correct Approach |
|-----------------|-------------------|
| Custom `<style>` tags in Blade | Use Tailwind utility classes |
| Hard-coded hex colors (#FF0000) | Use Tailwind color classes (text-red-500) |
| Random font sizes (18px, 22px) | Use text-sm, text-base, text-lg, text-xl |
| Inconsistent spacing (px-7, py-13) | Use Tailwind scale (2, 3, 4, 6, 8, 12) |
| Creating new .css files for one page | Reuse existing Tailwind utilities |
| Inline styles (`style="margin: 10px"`) | Use class attributes only |
| Bootstrap or other frameworks mixed in | Tailwind only (no conflicts) |
| Custom component without reuse plan | Create in components/ and document |

## Step-by-Step Workflow

### Phase 1: Discovery (Before Coding)

1. **Examine the existing design:**
   - Open `resources/views/` and identify similar pages
   - Check `resources/views/layouts/app.blade.php` (main wrapper)
   - Review `resources/views/components/` for available components

2. **Identify the design pattern:**
   - Is this a dashboard? (use [dashboard.blade.php](../../../resources/views/dashboard.blade.php) as reference)
   - Is this a form? (use form patterns from [resources/views/users/create.blade.php](../../../resources/views/users/create.blade.php))
   - Is this a table? (use table patterns from [resources/views/users/index.blade.php](../../../resources/views/users/index.blade.php))
   - Is this a report? (use report patterns from [resources/views/laporan/](../../../resources/views/laporan/))

3. **List design decisions needed:**
   - Layout (single column, 2-column, sidebar + main)
   - Components to reuse
   - Typography hierarchy
   - Color coding (if applicable)

### Phase 2: Implementation (Building)

1. **Wrap the page in the app layout:**
   ```blade
   <x-app-layout>
       <x-slot name="header">
           <h2 class="font-semibold text-xl text-gray-800 leading-tight">
               {{ __('Page Title') }}
           </h2>
       </x-slot>

       <div class="py-12">
           <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
               <!-- Content here -->
           </div>
       </div>
   </x-app-layout>
   ```

2. **Build card containers:**
   ```blade
   <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
       <div class="p-6 text-gray-900">
           <!-- Card content -->
       </div>
   </div>
   ```

3. **Apply typography from [Conventions](#3-typography-rules):**
   - Use semantic HTML (`<h1>`, `<h2>`, `<p>`)
   - Apply exact class combinations from the table

4. **Use spacing scale consistently:**
   - Between sections: `mb-6` or `mb-8`
   - Inside cards: `p-6`
   - Form groups: `space-y-4`

5. **Reuse components (DO NOT recreate):**
   ```blade
   <!-- Alerts -->
   <x-alert type="success" :message="session('success')" />

   <!-- Sidebar/Navbar -->
   <x-sidebar />
   <x-navbar />
   ```

6. **Validate color usage:**
   - Only colors from [Color Scheme](#2-color-scheme) table
   - Check hover/focus states for interactive elements
   - Ensure contrast meets accessibility standards (WCAG AA)

### Phase 3: Review (Before Committing)

1. **Visual consistency check:**
   - Compare your page side-by-side with similar existing pages
   - Check alignment of headers, spacing between sections
   - Verify all colors match the palette

2. **Component audit:**
   - Did you create custom `<style>` blocks? → Remove and use Tailwind
   - Did you use hex colors? → Convert to Tailwind classes
   - Did you hard-code spacing? → Use Tailwind scale
   - Did you duplicate existing components? → Replace with `<x-component-name />`

3. **Typography verification:**
   - Page title: `font-semibold text-2xl`?
   - Section headers: `font-semibold text-lg`?
   - Body text: `text-base text-gray-700`?
   - Labels: `text-sm text-gray-600`?

4. **Mobile responsiveness:**
   - Test on small screens (use Tailwind breakpoints: `sm:`, `md:`, `lg:`)
   - Sidebar collapses? Check `x-app-layout` responsive classes
   - Forms stack vertically? Check for `space-y-*`

5. **Documentation:**
   - If you added a new component, document it in [Component Reuse](#5-component-reuse)
   - If you extended the color scheme, document in [Color Scheme](#2-color-scheme)

## Common Patterns by Page Type

### Dashboard Pages
**File Reference:** [resources/views/dashboard.blade.php](../../../resources/views/dashboard.blade.php)

Pattern: Main title → Card containers → Grid of metrics

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-600">Metric Name</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-2">123</p>
                </div>
            </div>

            <!-- Chart/Content Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">Section Title</h3>
                <!-- Chart or content here -->
            </div>
        </div>
    </div>
</x-app-layout>
```

### Form Pages
**File Reference:** [resources/views/users/create.blade.php](../../../resources/views/users/create.blade.php)

Pattern: Form title → Form groups with validation → Submit button

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Create Item</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('items.store') }}" class="bg-white p-6 rounded-lg shadow-sm space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Field Name</label>
                    <input type="text" name="field" class="mt-1 block w-full rounded-md border-gray-300" 
                           value="{{ old('field') }}">
                    @error('field')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save
                    </button>
                    <a href="{{ route('items.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
```

### Table/List Pages
**File Reference:** [resources/views/users/index.blade.php](../../../resources/views/users/index.blade.php)

Pattern: List title → Filter/search → Table → Pagination

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Items</h2>
            <a href="{{ route('items.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                Add Item
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $item->name }}</td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    <a href="{{ route('items.edit', $item) }}" class="text-blue-600 hover:text-blue-800">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
```

## Troubleshooting

### Issue: Colors look different across pages
**Cause:** Using custom hex values or Bootstrap color names  
**Fix:** Replace all color references with Tailwind classes from [Color Scheme](#2-color-scheme)  
**Verify:** `grep -r "#[0-9A-F]" resources/views/` should return 0 results

### Issue: Spacing inconsistent (some sections tight, some loose)
**Cause:** Not using Tailwind spacing scale consistently  
**Fix:** Audit all padding/margin, replace with `p-6`, `mb-8`, `space-y-4` patterns  
**Verify:** Check existing cards and match their spacing exactly

### Issue: Fonts look random (different sizes throughout page)
**Cause:** Hard-coded `font-size` values or wrong Tailwind size classes  
**Fix:** Use only: `text-xs`, `text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`  
**Verify:** Compare with [Typography Rules](#3-typography-rules) table

### Issue: New component doesn't match existing ones
**Cause:** Not checking [Component Reuse](#5-component-reuse) list  
**Fix:** Search `resources/views/components/` before building anything new  
**Add:** If truly new, create in `resources/views/components/` and update this guide

## Quick Reference: File Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← Main wrapper, color scheme defined
│   └── guest.blade.php        ← Auth pages wrapper
├── components/
│   ├── alert.blade.php        ← Alert messages
│   ├── sidebar.blade.php      ← Navigation
│   ├── navbar.blade.php       ← Top bar
│   └── ...                    ← Reusable components
├── dashboard.blade.php        ← Dashboard reference
├── kandang/                   ← Pages by feature
├── produksi/
├── penjualan/
└── pengaturan/
```

## Next Steps

1. **Start coding:** Choose a page type above and follow the pattern
2. **Ask Copilot:** Describe your design challenge and reference this skill
3. **Submit for review:** Before merging, verify Phase 3 checklist
4. **Update documentation:** Add new components/patterns as they emerge
