let
  # Eski Nixpkgs snapshot (PHP 7.4 va MySQL 5.7 bor)
  old = import (builtins.fetchTarball {
    url = "https://github.com/NixOS/nixpkgs/archive/73bc3300ad02be21998a7c0e987592ca66df73f3.tar.gz";
  }) {};

  phpPkg = if builtins.hasAttr "php74" old then old.php74 else old.php;
  composerPkg = if builtins.hasAttr "php74Packages" old then old.php74Packages.composer else (old.phpPackages.composer or old.composer);
  mysqlPkg = old.mysql57;

in
old.mkShell {
  buildInputs = [
    phpPkg
    composerPkg
    mysqlPkg
  ];

  shellHook = ''
    echo "🚀 BrightBridge Development Environment"
    echo "----------------------------------------"
    echo "PHP version: $(php --version | head -n1)"
    echo "Composer version: $(composer --version)"
    echo "MySQL version: $(mysql --version)"
    echo ""
    echo "Loyihani ishga tushirish:"
    echo "  cd public_html && php artisan serve"
    echo "----------------------------------------"
  '';
}
