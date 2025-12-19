# GitHub Actions Workflows

## Which Workflow to Use?

### ✅ If your repository structure is:
```
repository-root/
├── .github/
│   └── workflows/
├── app/
├── composer.json
├── package.json
└── ... (Laravel files at root)
```
**Use:** `ci.yml` (already configured)

---

### ✅ If your repository structure is:
```
repository-root/
├── .github/
│   └── workflows/
├── employee-system/  ← Laravel app in subdirectory
│   ├── app/
│   ├── composer.json
│   ├── package.json
│   └── ...
└── docs/ (or other folders)
```
**Use:** `ci-subdirectory.yml.example`
1. Rename `ci-subdirectory.yml.example` to `ci.yml`
2. Delete the old `ci.yml`
3. If your subdirectory name is different than `employee-system`, update all instances in the workflow

---

## Errors Fixed

### ❌ Error 1: Node.js Lock File Not Found
**Solution:** Added `cache-dependency-path` to specify exact location of `package-lock.json`

### ❌ Error 2: Composer Install Failed
**Solution:** 
- Added all required PHP extensions
- Added `composer validate` check
- Added proper caching for faster builds
- Added `--optimize-autoloader` flag
- Removed problematic flags

## Additional Improvements

✅ **Proper caching** - Speeds up builds by caching composer and npm dependencies  
✅ **Database setup** - Creates SQLite database for testing  
✅ **Migrations** - Runs database migrations  
✅ **Two jobs** - Separate `tests` and `quality` jobs that run in parallel  
✅ **Better error handling** - Validates composer files before installation  
✅ **Supports both main and master branches**

## Testing Locally

Before pushing, test that everything works:

```bash
# Test Composer install
composer install --no-ansi --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Test NPM install and build
npm ci
npm run build

# Test Laravel commands
php artisan key:generate
php artisan migrate
vendor/bin/phpunit

# Test code style
vendor/bin/pint --test
```

## Need Help?

If you still get errors, check:
1. ✅ `composer.lock` exists and is committed
2. ✅ `package-lock.json` exists and is committed  
3. ✅ `.env.example` exists and is valid
4. ✅ PHP version in workflow matches your `composer.json` requirement
5. ✅ Node version is correct (currently set to v22)
