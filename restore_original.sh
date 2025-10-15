#!/bin/bash
# Backup current file
cp public_html/app/Http/Controllers/IndexController.php public_html/app/Http/Controllers/IndexController.php.backup

# Restore from initial commit
cd public_html
git show 68fce77:./app/Http/Controllers/IndexController.php > app/Http/Controllers/IndexController.php

echo "✓ Original IndexController restored from initial commit"
echo "✓ Backup saved as IndexController.php.backup"
