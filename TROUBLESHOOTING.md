# Troubleshooting Guide

## Cache Clearing After Restructuring

After restructuring components, Laravel and Livewire cache the old class locations. Follow these steps to clear all caches:

### Quick Fix (Run all commands in order):

```bash
# 1. Clear compiled files
php artisan clear-compiled

# 2. Clear application cache
php artisan cache:clear

# 3. Clear view cache
php artisan view:clear

# 4. Clear config cache
php artisan config:clear

# 5. Clear route cache
php artisan route:clear

# 6. Clear all optimization caches
php artisan optimize:clear

# 7. Regenerate autoload files
composer dump-autoload

# 8. Restart development server (if using php artisan serve)
# Press Ctrl+C to stop, then run:
php artisan serve
```

## Common Errors

### Error: "Failed to open stream: No such file or directory"

**Cause:** Laravel is looking for the old file location.

**Solution:**

```bash
composer dump-autoload
php artisan optimize:clear
```

### Error: "Class not found"

**Cause:** Autoload files not updated.

**Solution:**

```bash
composer dump-autoload
```

### Error: "Target class does not exist"

**Cause:** Route cache is outdated.

**Solution:**

```bash
php artisan route:clear
php artisan config:clear
```

### Drawer/Modal Not Showing

**Cause:** View cache or Livewire not recognizing new components.

**Solution:**

```bash
php artisan view:clear
php artisan optimize:clear
# Then hard refresh browser
```

## Development Workflow

When making structural changes (moving/renaming components):

1. **Make your changes**
2. **Clear all caches:**
    ```bash
    php artisan optimize:clear && composer dump-autoload
    ```
3. **Restart dev server** (if using `php artisan serve`)
4. **Hard refresh browser** (Ctrl+Shift+R)

## Production Deployment

Before deploying restructured code:

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Regenerate optimized autoload
composer dump-autoload --optimize

# 3. Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Restart PHP-FPM/Web Server
sudo systemctl restart php8.2-fpm  # adjust version as needed
```

**Created**: October 19, 2025  
**Project**: PLV Organization Events Scheduling System
