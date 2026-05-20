# Creating New Components

Guide for creating reusable Blade components that follow the design system.

## Directory Structure

All components go in `resources/views/components/`. Component files are automatically discoverable as `<x-component-name />`.

```
resources/views/components/
├── alert.blade.php           ← Status alert messages
├── sidebar.blade.php         ← Navigation sidebar
├── navbar.blade.php          ← Top navigation bar
├── button.blade.php          ← Reusable button (if building one)
├── card.blade.php            ← Card container (if building one)
└── form/
    ├── text-input.blade.php
    ├── select.blade.php
    └── error.blade.php
```

## Component Template Pattern

### Basic Component Structure

```blade
@props(['title', 'type' => 'info', 'class' => ''])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg {{ $class }}">
    <div class="p-6">
        <h3 class="font-semibold text-base text-gray-900 mb-2">{{ $title }}</h3>
        <div class="text-sm text-gray-700">
            {{ $slot }}
        </div>
    </div>
</div>
```

**Usage:**
```blade
<x-card title="My Card">
    Card content goes here
</x-card>
```

### Component with Slots

```blade
@props(['title', 'footer' => null])

<div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
    {{ $slot }}
    
    @if($footer)
        <div class="border-t border-gray-200 pt-4">
            {{ $footer }}
        </div>
    @endif
</div>
```

**Usage:**
```blade
<x-card>
    <x-slot name="header">
        <h3 class="font-semibold text-lg">Title</h3>
    </x-slot>
    
    Content here
    
    <x-slot name="footer">
        <button class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
    </x-slot>
</x-card>
```

## Common Component Examples

### Alert Component

**File:** `resources/views/components/alert.blade.php`

```blade
@props(['type' => 'info', 'title' => null, 'message' => ''])

@php
    $colors = [
        'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' => 'text-green-600', 'text' => 'text-green-800'],
        'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'icon' => 'text-yellow-600', 'text' => 'text-yellow-800'],
        'error'   => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => 'text-red-600', 'text' => 'text-red-800'],
        'info'    => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'text-blue-600', 'text' => 'text-blue-800'],
    ];
    $color = $colors[$type] ?? $colors['info'];
@endphp

<div class="{{ $color['bg'] }} border {{ $color['border'] }} rounded-lg p-4 flex gap-3">
    <i class="fas fa-info-circle {{ $color['icon'] }} mt-1"></i>
    <div>
        @if($title)
            <p class="font-semibold {{ $color['text'] }}">{{ $title }}</p>
        @endif
        <p class="text-sm {{ $color['text'] }}">{{ $message ?? $slot }}</p>
    </div>
</div>
```

**Usage:**
```blade
<x-alert type="success" title="Success!" message="Item saved successfully" />

<x-alert type="error" message="An error occurred" />
```

### Form Input Component

**File:** `resources/views/components/form/text-input.blade.php`

```blade
@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'error' => null,
    'class' => '',
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif
    
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        class="mt-1 block w-full rounded-md border-gray-300 {{ $class }} @error($name) border-red-500 @enderror"
    >
    
    @error($name)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
```

**Usage:**
```blade
<x-form.text-input 
    label="Email Address" 
    name="email" 
    type="email" 
    placeholder="user@example.com"
    required
/>
```

### Status Badge Component

**File:** `resources/views/components/status-badge.blade.php`

```blade
@props(['status'])

@php
    $styles = [
        'active'   => 'bg-green-100 text-green-800',
        'inactive' => 'bg-gray-100 text-gray-800',
        'pending'  => 'bg-yellow-100 text-yellow-800',
        'error'    => 'bg-red-100 text-red-800',
    ];
    $style = $styles[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $style }}">
    {{ ucfirst($status) }}
</span>
```

**Usage:**
```blade
<x-status-badge status="active" />
<x-status-badge status="pending" />
```

### Button Component

**File:** `resources/views/components/button.blade.php`

```blade
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
])

@php
    $variants = [
        'primary'   => 'bg-blue-600 hover:bg-blue-700 text-white',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
        'danger'    => 'bg-red-600 hover:bg-red-700 text-white',
        'success'   => 'bg-green-600 hover:bg-green-700 text-white',
    ];
    
    $sizes = [
        'sm' => 'px-3 py-1 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ];
    
    $class = $variants[$variant] ?? $variants['primary'];
    $class .= ' ' . ($sizes[$size] ?? $sizes['md']);
    $class .= ' rounded-lg font-medium inline-flex items-center gap-2 transition-colors';
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $class }}" {{ $attributes }}>
        @if($icon) <i class="fas fa-{{ $icon }}"></i> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $class }}" {{ $attributes }}>
        @if($icon) <i class="fas fa-{{ $icon }}"></i> @endif
        {{ $slot }}
    </button>
@endif
```

**Usage:**
```blade
<x-button>Save</x-button>
<x-button variant="secondary">Cancel</x-button>
<x-button variant="danger" size="sm" icon="trash">Delete</x-button>
<x-button href="/items" icon="arrow-left">Back</x-button>
```

## Best Practices

### ✅ DO

- **Use flexible `$slot`:** Allow content customization
- **Use `@props([...])` syntax:** Modern Laravel way (8.30+)
- **Provide sensible defaults:** `$type = 'info'`, not required parameters
- **Use semantic HTML:** `<button>`, `<a>`, `<label>`
- **Include accessibility:** `aria-*` attributes where needed
- **Document with comments:** Show usage example at top
- **Keep components small:** Single responsibility principle
- **Use Tailwind utility only:** No inline styles or custom CSS

### ❌ DON'T

- **Create bloated mega-components:** Split into smaller pieces
- **Use hard-coded colors:** Use Tailwind variables/maps
- **Add custom CSS files:** Only Tailwind utilities
- **Nest too deeply:** Components should be composable
- **Ignore accessibility:** Add labels, ARIA, semantic HTML
- **Hard-code spacing:** Use Tailwind scale always
- **Create component duplicates:** Check if similar exists

## Updating Existing Components

When modifying a component:

1. **Check for usages:** `grep -r "<x-component-name" resources/views/`
2. **Maintain backward compatibility:** Don't break existing props
3. **Add new props as optional:** With sensible defaults
4. **Update documentation:** Show new usage examples
5. **Test all usages:** Ensure no visual regressions

## Testing Your Component

Create a test page to verify:

```blade
<!-- resources/views/components-test.blade.php -->
<x-app-layout>
    <div class="space-y-6">
        <x-card title="Test Card 1">Content here</x-card>
        <x-card title="Test Card 2" class="border border-gray-300">Different style</x-card>
        
        <x-alert type="success" message="Success test" />
        <x-alert type="error" message="Error test" />
        
        <x-status-badge status="active" />
        <x-status-badge status="pending" />
    </div>
</x-app-layout>
```

Visit `http://localhost:8000/test-components` to visually verify.

## Component Checklist

Before committing a new component:

- [ ] File placed in `resources/views/components/`
- [ ] File name matches usage: `button.blade.php` → `<x-button />`
- [ ] Uses `@props([...])` for parameters
- [ ] Has `{{ $slot }}` for content flexibility
- [ ] No custom `<style>` tags or inline styles
- [ ] Only Tailwind utility classes used
- [ ] Colors from design palette only
- [ ] Spacing uses Tailwind scale
- [ ] Semantic HTML elements used
- [ ] Accessibility attributes included
- [ ] Example usage documented in comments
- [ ] Tested across responsive breakpoints
- [ ] Added to SKILL.md component reference
