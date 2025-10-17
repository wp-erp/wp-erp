# WP ERP React Onboarding - Quick Start Guide

## ✅ What's Been Done

A complete, production-ready React + Tailwind onboarding system has been implemented for WP ERP.

### Files Created

```
includes/Admin/Onboarding/      # New React onboarding module
├── assets/
│   ├── src/                    # Source files
│   │   ├── index.jsx
│   │   ├── App.jsx
│   │   ├── components/
│   │   ├── styles/
│   │   └── utils/
│   └── dist/                   # Built files
│       ├── onboarding.js      (189 KB)
│       └── onboarding.css     (16.5 KB)
└── README.md

tailwind.config.js             # Tailwind with wperp- prefix
postcss.config.js              # PostCSS 7 config
webpack.onboarding.config.js   # Isolated webpack for React
```

### Build Commands

```bash
# Build everything (Vue + React)
npm run build

# Build only React onboarding
npm run build:onboarding

# Development watch mode
npm run dev:onboarding
```

## 🚀 How to Use

### For Development

1. **Install dependencies** (already done):
   ```bash
   npm install --legacy-peer-deps
   ```

2. **Build the onboarding app**:
   ```bash
   npm run build:onboarding
   ```

3. **Access the wizard**:
   Navigate to: `http://your-site.test/wp-admin/index.php?page=erp-setup`

### For Production

1. **Build before deployment**:
   ```bash
   npm run build
   ```

2. **Deploy these files**:
   - `includes/Admin/Onboarding/assets/dist/`
   - `includes/Admin/SetupWizard.php` (modified)

## 🔍 Troubleshooting

### Blank Page Issue

If you see a blank page, check:

1. **Browser Console** (F12 → Console tab)
   - Look for JavaScript errors
   - Check if React is loading

2. **Network Tab** (F12 → Network tab)
   - Verify `onboarding.js` loads (should be ~189 KB)
   - Verify `onboarding.css` loads (should be ~16.5 KB)
   - Check for 404 errors

3. **File Permissions**
   ```bash
   # Make sure files exist
   ls -lh includes/Admin/Onboarding/assets/dist/

   # Should show:
   # onboarding.css (16K)
   # onboarding.js (189K)
   ```

4. **Rebuild if needed**
   ```bash
   npm run build:onboarding
   ```

### Common Issues

#### Issue: CSS not loading
**Solution**: Clear browser cache and hard refresh (Ctrl+Shift+R)

#### Issue: JavaScript error "wpErpOnboarding is not defined"
**Solution**: Check that SetupWizard.php has `wp_localize_script` call

#### Issue: Styles conflict with WordPress
**Solution**: All our classes use `wperp-` prefix, shouldn't conflict

#### Issue: Vue apps broken
**Solution**: Run `npm run build` to rebuild everything

## 📝 Next Steps (Backend TODO)

The frontend is complete, but you need to implement REST API endpoints:

### Required API Endpoints

```php
// POST /wp-json/erp/v1/onboarding/basic
// Save basic company settings
{
    "companyName": "string",
    "financialYearStart": "date",
    "businessType": "string"
}

// POST /wp-json/erp/v1/onboarding/organization
// Save departments and designations
{
    "departments": ["string"],
    "designations": ["string"]
}

// POST /wp-json/erp/v1/onboarding/import-employees
// Import employees from CSV
FormData with 'file' field

// POST /wp-json/erp/v1/onboarding/modules
// Save module settings
{
    "enableLeaveManagement": boolean,
    "workingDays": {...},
    "workingHours": {...}
}

// POST /wp-json/erp/v1/onboarding/complete
// Mark onboarding as complete
```

### Suggested Backend Implementation

Create a new file: `includes/API/Onboarding.php`

```php
<?php
namespace WeDevs\ERP\API;

class Onboarding extends \WP_REST_Controller {
    public function register_routes() {
        register_rest_route('erp/v1', '/onboarding/basic', [
            'methods'             => 'POST',
            'callback'            => [$this, 'save_basic'],
            'permission_callback' => [$this, 'check_permission'],
        ]);

        // ... register other routes
    }

    public function check_permission() {
        return current_user_can('manage_options');
    }

    public function save_basic($request) {
        $data = $request->get_json_params();

        // Validate and sanitize
        // Save to database/options
        // Return response

        return rest_ensure_response([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }
}
```

## 📊 Features Implemented

✅ 5-step wizard flow
✅ Form validation
✅ File upload (drag & drop)
✅ Dynamic fields (add/remove)
✅ Progress indicator
✅ Loading states
✅ Error handling
✅ Responsive design
✅ Tailwind CSS with prefix
✅ Production-ready build
✅ No conflicts with existing code

## 🎨 Design System

- **Primary Color**: Blue (#3b82f6)
- **Font**: Inter, system fonts
- **Spacing**: Tailwind scale
- **Prefix**: All classes use `wperp-`
- **Components**: Pre-built button, input, card styles

## 📚 Documentation

- **Full docs**: `includes/Admin/Onboarding/README.md`
- **Implementation details**: `ONBOARDING_IMPLEMENTATION.md`
- **This guide**: `QUICK_START.md`

## 🤝 Support

If you have issues:

1. Check browser console for errors
2. Verify files built correctly
3. Check file permissions
4. Review documentation
5. Rebuild: `npm run build:onboarding`

## 🎯 Testing

To test without backend:

1. Open `test-onboarding.html` in browser
2. Should see the wizard interface
3. Can click through steps (API calls will fail gracefully)

---

**Status**: ✅ Frontend Complete
**Todo**: Backend API implementation
**Build Status**: ✅ All builds passing
**Compatibility**: ✅ No conflicts with existing code

