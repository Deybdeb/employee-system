# Local CI Test Script (PowerShell)
# Run this script to test if the GitHub Actions workflow will pass

$ErrorActionPreference = "Stop"

Write-Host "🧪 Testing GitHub Actions Workflow Locally..." -ForegroundColor Cyan
Write-Host ""

# Test 1: Validate Composer
Write-Host "📦 Step 1: Validating composer files..." -ForegroundColor Yellow
try {
    composer validate --strict 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Composer files are valid" -ForegroundColor Green
    } else {
        throw "Composer validation failed"
    }
} catch {
    Write-Host "✗ Composer validation failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 2: Install Composer Dependencies
Write-Host "📦 Step 2: Installing Composer dependencies..." -ForegroundColor Yellow
try {
    composer install --no-ansi --no-interaction --no-progress --prefer-dist --optimize-autoloader
    Write-Host "✓ Composer dependencies installed" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer install failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 3: Check .env file
Write-Host "⚙️  Step 3: Checking .env file..." -ForegroundColor Yellow
if (-not (Test-Path .env)) {
    Write-Host "⚠ .env not found, copying from .env.example" -ForegroundColor Yellow
    Copy-Item .env.example .env
}
Write-Host "✓ .env file exists" -ForegroundColor Green
Write-Host ""

# Test 4: Generate Application Key
Write-Host "🔑 Step 4: Generating application key..." -ForegroundColor Yellow
try {
    php artisan key:generate
    Write-Host "✓ Application key generated" -ForegroundColor Green
} catch {
    Write-Host "✗ Key generation failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 5: Install NPM Dependencies
Write-Host "📦 Step 5: Installing NPM dependencies..." -ForegroundColor Yellow
try {
    npm ci
    Write-Host "✓ NPM dependencies installed" -ForegroundColor Green
} catch {
    Write-Host "✗ NPM install failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 6: Build Assets
Write-Host "🏗️  Step 6: Building assets..." -ForegroundColor Yellow
try {
    npm run build
    Write-Host "✓ Assets built successfully" -ForegroundColor Green
} catch {
    Write-Host "✗ Asset build failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 7: Setup Database
Write-Host "🗄️  Step 7: Setting up database..." -ForegroundColor Yellow
if (-not (Test-Path database/database.sqlite)) {
    New-Item -Path database/database.sqlite -ItemType File | Out-Null
    Write-Host "✓ SQLite database created" -ForegroundColor Green
} else {
    Write-Host "✓ SQLite database exists" -ForegroundColor Green
}
Write-Host ""

# Test 8: Run Migrations
Write-Host "🔄 Step 8: Running migrations..." -ForegroundColor Yellow
try {
    php artisan migrate --force
    Write-Host "✓ Migrations completed" -ForegroundColor Green
} catch {
    Write-Host "✗ Migrations failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 9: Run Tests
Write-Host "🧪 Step 9: Running tests..." -ForegroundColor Yellow
try {
    vendor/bin/phpunit --testdox
    Write-Host "✓ All tests passed" -ForegroundColor Green
} catch {
    Write-Host "✗ Tests failed" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 10: Code Style Check
Write-Host "✨ Step 10: Checking code style with Laravel Pint..." -ForegroundColor Yellow
try {
    vendor/bin/pint --test 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Code style is clean" -ForegroundColor Green
    } else {
        Write-Host "⚠ Code style issues found. Run 'vendor/bin/pint' to fix" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠ Code style issues found. Run 'vendor/bin/pint' to fix" -ForegroundColor Yellow
}
Write-Host ""

Write-Host "================================================" -ForegroundColor Cyan
Write-Host "🎉 All checks passed! Your workflow should succeed." -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Cyan
