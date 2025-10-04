{ pkgs, ... }:

let
  system = builtins.currentSystem;

  old = import (builtins.fetchTarball {
    url = "https://github.com/NixOS/nixpkgs/archive/73bc3300ad02be21998a7c0e987592ca66df73f3.tar.gz";
    # Tavsiya: reproducible bo'lishi uchun bu yerga sha256 qo'shing.
  }) {};

  phpPkg = if builtins.hasAttr "php74" old then old.php74 else old.php;
  composerPkg = if builtins.hasAttr "php74Packages" old then old.php74Packages.composer else (old.phpPackages.composer or old.composer);
  mysqlPkg = old.mysql57;

in
{
  packages = [
    phpPkg
    composerPkg
    mysqlPkg
  ];
}
