#!/bin/bash

# Dashboard Analytics Setup Script
# Run this script to setup the dashboard analytics system
# Usage: bash setup-dashboard.sh

set -e

echo "================================"
echo "🚀 Dashboard Analytics Setup"
echo "================================"
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ NPM is not installed. Please install Node.js and NPM first."
    exit 1
fi

echo "✅ Prerequisites check passed"
echo ""

# Step 1: Install NPM dependencies
echo "📦 Step 1: Installing NPM dependencies..."
npm install apexcharts chart.js leaflet leaflet.markercluster
echo "✅ NPM dependencies installed"
echo ""

# Step 2: Install PHP dependencies (if needed)
echo "📦 Step 2: Checking PHP dependencies..."
composer require filament/filament:^2.17 --no-update || true
echo "✅ PHP dependencies checked"
echo ""

# Step 3: Build assets
echo "🔨 Step 3: Building assets..."
npm run build
echo "✅ Assets built successfully"
echo ""

# Step 4: Clear caches
echo "🧹 Step 4: Clearing caches..."
php artisan optimize:clear
php artisan cache:clear
echo "✅ Caches cleared"
echo ""

# Step 5: Publish Filament assets
echo "📦 Step 5: Publishing Filament assets..."
php artisan filament:install
php artisan vendor:publish --tag=filament-config --force
echo "✅ Filament assets published"
echo ""

# Step 6: Run migrations (optional)
read -p "Do you want to run migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate
    echo "✅ Migrations completed"
else
    echo "⏭️  Skipping migrations"
fi
echo ""

# Step 7: Optimize Laravel
echo "⚡ Step 7: Optimizing Laravel..."
php artisan optimize
echo "✅ Laravel optimized"
echo ""

echo "================================"
echo "✨ Setup Completed Successfully!"
echo "================================"
echo ""
echo "📝 Next Steps:"
echo "1. Add export routes to routes/web.php"
echo "2. Register CSS in your blade layout"
echo "3. Run: php artisan serve"
echo "4. Visit: http://localhost:8000/admin"
echo ""
echo "📚 Documentation:"
echo "   - DASHBOARD_DOCUMENTATION.md"
echo "   - IMPLEMENTATION_CHECKLIST.md"
echo "   - EXPORT_ROUTES_SETUP.md"
echo ""
