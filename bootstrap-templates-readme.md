# Bootstrap 5 UI Templates

A comprehensive collection of responsive Bootstrap 5 UI templates and components for modern web applications.

## 📁 Project Structure

```
/workspace/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom Bootstrap extensions
│   └── js/
│       └── app.js             # UI interactions JavaScript
├── includes/
│   ├── header.php             # Responsive navigation header
│   └── footer.php             # Common footer with scripts
└── pages/templates/
    ├── form-template.php      # Form components & validation
    ├── table-template.php     # Data tables with features
    ├── modal-template.php     # Modal dialogs & interactions
    ├── dashboard-template.php # Dashboard layout & widgets
    └── card-template.php      # Card components & layouts
```

## 🚀 Features

### CSS Enhancements (`assets/css/style.css`)
- **Custom Variables**: Centralized color scheme and design tokens
- **Enhanced Components**: Extended Bootstrap components with modern styling
- **Responsive Utilities**: Mobile-first responsive design patterns
- **Animation Classes**: Smooth transitions and hover effects
- **Print Styles**: Optimized print layouts

### JavaScript Interactions (`assets/js/app.js`)
- **Form Validation**: Real-time validation with Bootstrap feedback
- **Data Tables**: Sorting, filtering, and search functionality
- **Modal Management**: Programmatic modal control
- **Search & Filters**: Dynamic content filtering
- **Alert System**: Toast notifications and alerts
- **Utility Functions**: Date formatting, currency formatting, etc.

### Responsive Header (`includes/header.php`)
- **Navigation Menu**: Multi-level dropdown navigation
- **Search Bar**: Integrated search functionality
- **User Profile**: Avatar and dropdown menu
- **Notifications**: Badge indicators and dropdown
- **Mobile Responsive**: Collapsible navigation for mobile

### Footer Component (`includes/footer.php`)
- **Newsletter Signup**: Email subscription form
- **Quick Links**: Site navigation footer
- **Social Links**: Social media icons
- **Back to Top**: Smooth scroll to top button
- **Loading Overlay**: Form submission feedback

## 🎨 Template Components

### 1. Form Template (`form-template.php`)
**Features:**
- Contact forms with validation
- User registration forms
- Advanced search & filter forms
- File upload with drag & drop
- Settings forms with toggles
- Real-time validation feedback

**Components:**
- Input groups with icons
- Form validation with error messages
- File upload with preview
- Checkbox and radio groups
- Switch toggles for settings

### 2. Table Template (`table-template.php`)
**Features:**
- Product inventory tables
- Order management tables
- Sorting functionality
- Search and filtering
- Pagination
- Export functionality
- Print-optimized layouts

**Components:**
- Responsive table design
- Action buttons and dropdowns
- Status badges
- Progress indicators
- Data visualization

### 3. Modal Template (`modal-template.php`)
**Features:**
- Multiple modal sizes (sm, lg, xl, fullscreen)
- Form modals
- Image gallery modals
- Video player modals
- Loading modals
- Nested modals
- Tabbed modals
- Confirmation dialogs

**Components:**
- Modal events handling
- Keyboard navigation
- Focus management
- Stacking support
- Dynamic content loading

### 4. Dashboard Template (`dashboard-template.php`)
**Features:**
- Key performance indicators
- Revenue charts (placeholder)
- Recent activity timeline
- Recent orders table
- Quick actions panel
- Top products list
- System status monitoring

**Components:**
- Stat cards with icons
- Progress bars
- Activity timeline
- Interactive charts
- Real-time updates simulation

### 5. Card Template (`card-template.php`)
**Features:**
- Profile cards
- Product cards
- Stat cards
- Comment cards
- Event cards
- Weather cards
- Interactive cards

**Components:**
- Hover effects
- Image overlays
- Progress indicators
- Badge systems
- Action buttons
- Dropdown menus

## 📱 Responsive Design

All templates are fully responsive and optimized for:
- **Mobile Devices** (320px+)
- **Tablets** (768px+)
- **Desktop** (992px+)
- **Large Screens** (1200px+)

### Breakpoint Optimization
```css
/* Mobile First Approach */
@media (max-width: 576px) { /* Extra small devices */ }
@media (max-width: 768px) { /* Small devices (tablets) */ }
@media (max-width: 992px) { /* Medium devices (desktops) */ }
@media (max-width: 1200px) { /* Large devices */ }
```

## 🎯 Key Features

### Accessibility
- ARIA labels and roles
- Keyboard navigation support
- Screen reader friendly
- High contrast color schemes
- Focus indicators

### Performance
- Optimized CSS and JavaScript
- Lazy loading ready
- Print-optimized styles
- Minimal external dependencies

### Customization
- CSS custom properties
- Modular JavaScript classes
- Easy color scheme changes
- Component-based architecture

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- IE11+ (with polyfills)

## 🔧 Usage Examples

### Including Header and Footer
```php
<?php include '../includes/header.php'; ?>

<!-- Your page content here -->

<?php include '../includes/footer.php'; ?>
```

### Using JavaScript UI Class
```javascript
// Initialize UI components
const ui = new BootstrapUI();

// Show alert
ui.showAlert('success', 'Operation completed!');

// Show modal
ui.showModal('myModal');

// Format currency
const price = ui.formatCurrency(1234.56, 'USD'); // $1,234.56
```

### Custom CSS Variables
```css
:root {
    --primary-color: #your-brand-color;
    --secondary-color: #your-secondary-color;
    --border-radius: 0.5rem;
}
```

## 📋 Dependencies

### CDN Links (Included)
- **Bootstrap 5.3.0**: CSS and JavaScript
- **Bootstrap Icons**: Icon library
- **jQuery 3.6.0**: Enhanced functionality (optional)
- **DataTables**: Advanced table features
- **Chart.js**: Data visualization

### Custom Assets
- Custom CSS extensions
- JavaScript UI interactions
- PHP includes for header/footer

## 🎨 Color Scheme

### Primary Colors
```css
--primary-color: #0d6efd    /* Bootstrap Primary */
--secondary-color: #6c757d  /* Bootstrap Secondary */
--success-color: #198754    /* Bootstrap Success */
--danger-color: #dc3545     /* Bootstrap Danger */
--warning-color: #ffc107    /* Bootstrap Warning */
--info-color: #0dcaf0       /* Bootstrap Info */
```

## 🚀 Getting Started

1. **Include the files** in your project
2. **Customize** the CSS variables for your brand
3. **Initialize** the JavaScript UI class
4. **Use the templates** as starting points
5. **Extend** the components as needed

## 📖 Documentation

Each template file includes:
- Inline documentation
- Commented code sections
- Usage examples
- Interactive demonstrations

## 🔄 Browser Compatibility

- **Chrome**: Full support
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **IE11**: Partial support (requires polyfills)

## 📝 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Feel free to contribute by:
- Adding new template components
- Improving accessibility features
- Enhancing responsive design
- Adding new interaction patterns

---

**Built with ❤️ using Bootstrap 5**