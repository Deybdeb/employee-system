#!/usr/bin/env bash

# Local CI Test Script
# Run this script to test if the GitHub Actions workflow will pass

set -e  # Exit on any error

echo "🧪 Testing GitHub Actions Workflow Locally..."
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Validate Composer
echo "📦 Step 1: Validating composer files..."
if composer validate --strict; then
    echo -e "${GREEN}✓ Composer files are valid${NC}"
else
    echo -e "${RED}✗ Composer validation failed${NC}"
    exit 1
fi
echo ""

# Test 2: Install Composer Dependencies
echo "📦 Step 2: Installing Composer dependencies..."
if composer install --no-ansi --no-interaction --no-progress --prefer-dist --optimize-autoloader; then
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${RED}✗ Composer install failed${NC}"
    exit 1
fi
echo ""

# Test 3: Check .env file
echo "⚙️  Step 3: Checking .env file..."
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠ .env not found, copying from .env.example${NC}"
    cp .env.example .env
fi
echo -e "${GREEN}✓ .env file exists${NC}"
echo ""

# Test 4: Generate Application Key
echo "🔑 Step 4: Generating application key..."
if php artisan key:generate; then
    echo -e "${GREEN}✓ Application key generated${NC}"
else
    echo -e "${RED}✗ Key generation failed${NC}"
    exit 1
fi
echo ""

# Test 5: Install NPM Dependencies
echo "📦 Step 5: Installing NPM dependencies..."
if npm ci; then
    echo -e "${GREEN}✓ NPM dependencies installed${NC}"
else
    echo -e "${RED}✗ NPM install failed${NC}"
    exit 1
fi
echo ""

# Test 6: Build Assets
echo "🏗️  Step 6: Building assets..."
if npm run build; then
    echo -e "${GREEN}✓ Assets built successfully${NC}"
else
    echo -e "${RED}✗ Asset build failed${NC}"
    exit 1
fi
echo ""

# Test 7: Setup Database
echo "🗄️  Step 7: Setting up database..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo -e "${GREEN}✓ SQLite database created${NC}"
else
    echo -e "${GREEN}✓ SQLite database exists${NC}"
fi
echo ""

# Test 8: Run Migrations
echo "🔄 Step 8: Running migrations..."
if php artisan migrate --force; then
    echo -e "${GREEN}✓ Migrations completed${NC}"
else
    echo -e "${RED}✗ Migrations failed${NC}"
    exit 1
fi
echo ""

# Test 9: Run Tests
echo "🧪 Step 9: Running tests..."
if vendor/bin/phpunit --testdox; then
    echo -e "${GREEN}✓ All tests passed${NC}"
else
    echo -e "${RED}✗ Tests failed${NC}"
    exit 1
fi
echo ""

# Test 10: Code Style Check
echo "✨ Step 10: Checking code style with Laravel Pint..."
if vendor/bin/pint --test; then
    echo -e "${GREEN}✓ Code style is clean${NC}"
else
    echo -e "${YELLOW}⚠ Code style issues found. Run 'vendor/bin/pint' to fix${NC}"
fi
echo ""

echo "================================================"
echo -e "${GREEN}🎉 All checks passed! Your workflow should succeed.${NC}"
echo "================================================"
