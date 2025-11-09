# Quill.js Integration

This project uses Quill.js as a rich text editor for post content management, integrated with Livewire and Alpine.js.

## Installation

Quill.js is installed via npm and bundled with Vite.

```bash
npm install
```

## File Structure

```
resources/
├── js/
│   ├── app.js                 # Main JavaScript entry point
│   ├── quill-editor.js        # Quill editor Alpine.js component
│   └── README-QUILL.md        # This file
└── css/
    ├── app.css                # Main CSS file (imports quill-theme.css)
    └── quill-theme.css        # Quill dark mode and custom styles
```

## Usage

### In Blade Templates

To use the Quill editor in your Livewire components:

```blade
<div x-data="quillEditor(@entangle('content'))" x-init="initQuill()">
    <flux:label>Content</flux:label>
    <div x-ref="editor" class="bg-white dark:bg-neutral-900" style="min-height: 200px;"></div>
    <input type="hidden" x-model="content" required>
</div>
```

### Livewire Property

In your Livewire component, define the property:

```php
#[Validate('required|string')]
public string $content = '';
```

## Features

### Rich Text Editing

The editor includes comprehensive formatting options:

- **Headers** (H1-H6)
- **Font styles** and sizes
- **Text formatting**: Bold, Italic, Underline, Strike-through
- **Colors**: Text color and background color
- **Lists**: Ordered and bullet lists
- **Indentation** controls
- **Text alignment** options
- **Block quotes** and code blocks
- **Media embedding**: Links, images, and videos
- **Subscript/Superscript**
- **RTL text support**

### Dark Mode

The editor automatically adapts to your application's dark mode with custom styling in `quill-theme.css`.

### Two-way Data Binding

- Content automatically syncs with Livewire properties via `@entangle()`
- Changes in the editor update Livewire immediately
- External updates to content (e.g., when editing) update the editor

### Custom Scrollbar

The editor includes a custom scrollbar that matches the application's design system.

## Configuration

### Toolbar Customization

To customize the toolbar, edit `resources/js/quill-editor.js`:

```javascript
const toolbarOptions = [
    // Add or remove toolbar options here
    [{ 'header': [1, 2, 3, false] }],  // Example: Only H1, H2, H3
    ['bold', 'italic'],                 // Example: Only bold and italic
];
```

### Editor Dimensions

To change editor height, modify `resources/css/quill-theme.css`:

```css
.ql-editor {
    min-height: 200px;  /* Change minimum height */
    max-height: 400px;  /* Change maximum height */
}
```

### Dark Mode Colors

Customize dark mode colors in `resources/css/quill-theme.css` by modifying the `.dark` selectors.

## Build

The Quill editor is bundled with your application assets:

```bash
# Development
npm run dev

# Production
npm run build
```

## Dependencies

- **quill**: ^2.0.3
- **Alpine.js**: Included with Livewire
- **Livewire**: For reactive data binding

## Troubleshooting

### Editor not showing

1. Make sure you've run `npm install`
2. Ensure Vite is running (`npm run dev`)
3. Check browser console for errors

### Content not syncing

1. Verify the Livewire property name matches the `@entangle()` parameter
2. Check that the property has proper validation rules
3. Ensure Alpine.js is loaded

### Styling issues

1. Clear browser cache
2. Rebuild assets: `npm run build`
3. Check that `quill-theme.css` is imported in `app.css`

## Documentation

- [Quill.js Documentation](https://quilljs.com/docs/)
- [Livewire Documentation](https://livewire.laravel.com/)
- [Alpine.js Documentation](https://alpinejs.dev/)
