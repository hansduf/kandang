# Design System Reference

This document contains quick-reference cards for the Hans Jaya Poultry design system.

## Color Palette

### UI Colors
```
Primary:   bg-blue-900 (text-white)
Secondary: bg-white
Background: bg-gray-50, bg-gray-100
Text Dark: text-gray-900, text-gray-800
Text Light: text-gray-600, text-gray-700
Border: border-gray-200, border-gray-300
Shadow: shadow-sm, shadow-md
```

### Interactive States
```
Hover Links: hover:text-blue-600
Hover Buttons: hover:bg-blue-700, hover:bg-gray-300
Focus Ring: focus:ring-2 focus:ring-blue-500
Disabled: opacity-50, cursor-not-allowed
```

## Typography Scale

```
Text Element          | Class Pattern              | Example
─────────────────────────────────────────────────────────────
Page Title           | font-semibold text-2xl     | Dashboard
Section Header       | font-semibold text-lg      | Recent Sales
Card Title           | font-semibold text-base    | Items
Body Text            | text-base text-gray-700    | Normal paragraph
Label/Subtitle       | text-sm text-gray-600      | Form label
Helper Text          | text-xs text-gray-500      | Instructions, hints
Link                 | text-blue-600 hover:...    | Navigation, actions
Button Text          | font-medium text-sm        | Action buttons
```

## Spacing Scale

```
Use only these values from Tailwind:
2, 3, 4, 6, 8, 12, 16, 20, 24, 28, 32...

Common Usage:
px-4, py-8            → 1rem horizontal, 2rem vertical
p-6                   → 1.5rem all sides
space-y-4             → 1rem gap between vertical items
gap-6                 → 1.5rem gap in grids
mb-8                  → 2rem margin-bottom
mt-2                  → 0.5rem margin-top
```

## Component Sizing

```
Max Content Width: max-w-7xl (80rem)
Card Padding: p-6 (1.5rem)
Input Height: py-2 or py-3 (0.5rem-0.75rem padding)
Icon Size: w-5 h-5 or w-6 h-6 (Font Awesome)
Small Button: px-3 py-2
Large Button: px-4 py-3
```

## Shadows & Borders

```
Cards: shadow-sm sm:rounded-lg
Hover: shadow-md on hover
Border: border border-gray-200
Rounded: rounded-lg or rounded-md
```

## Responsive Breakpoints (Tailwind)

```
sm:   640px    (small devices)
md:   768px    (tablets)
lg:   1024px   (desktops)
xl:   1280px   (large screens)

Example:
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    Stacks on mobile (1 col), 3 cols on tablets and up
</div>
```

## Common Patterns

### Card Container
```blade
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        Content here
    </div>
</div>
```

### Page Header
```blade
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Page Title') }}
    </h2>
</x-slot>
```

### Form Input Group
```blade
<div>
    <label class="block text-sm font-medium text-gray-700">Label</label>
    <input type="text" class="mt-1 block w-full rounded-md border-gray-300">
</div>
```

### Button Group
```blade
<div class="flex gap-3">
    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Save
    </button>
    <a href="#" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
        Cancel
    </a>
</div>
```

### Table Header
```blade
<tr class="bg-gray-100 border-b border-gray-200">
    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
        Column Name
    </th>
</tr>
```

### Table Row (with hover)
```blade
<tr class="border-b border-gray-200 hover:bg-gray-50">
    <td class="px-6 py-4 text-sm text-gray-700">
        Data
    </td>
</tr>
```

## Icons (Font Awesome 6)

```html
<!-- Common icons -->
<i class="fas fa-home"></i>           <!-- Home -->
<i class="fas fa-plus"></i>           <!-- Add -->
<i class="fas fa-pencil"></i>         <!-- Edit -->
<i class="fas fa-trash"></i>          <!-- Delete -->
<i class="fas fa-search"></i>         <!-- Search -->
<i class="fas fa-chart-line"></i>     <!-- Chart -->
<i class="fas fa-boxes"></i>          <!-- Inventory -->

<!-- With sizing -->
<i class="fas fa-home text-lg"></i>   <!-- Large icon -->
<i class="fas fa-home text-sm"></i>   <!-- Small icon -->
```

## Utilities Worth Remembering

```
flex justify-between items-center    → Horizontal layout with space-between
grid grid-cols-3 gap-6               → 3-column grid with gaps
w-full                               → 100% width
overflow-hidden                      → Hide overflow content
transition-all duration-300          → Smooth animations
opacity-50                           → 50% transparency
cursor-pointer                       → Pointer cursor on hover
```
