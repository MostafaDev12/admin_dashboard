param([Parameter(ValueFromRemainingArguments = $true)][string[]]$ArtisanArgs)
$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$phpBinary = 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe'
if (-not (Test-Path -LiteralPath $phpBinary)) { $phpBinary = (Get-Command php -ErrorAction Stop).Source }
Set-Location -LiteralPath $projectRoot
if ($ArtisanArgs[0] -eq 'serve') {
    # The built-in server must use the same environment and SQLite extension.
    $env:APP_ENV = 'cms'
    & $phpBinary -d extension=pdo_sqlite -d upload_max_filesize=20M -d post_max_size=205M -d max_file_uploads=10 -S 127.0.0.1:8787 -t public server.php
    exit $LASTEXITCODE
}
& $phpBinary -d extension=pdo_sqlite artisan --env=cms @ArtisanArgs
exit $LASTEXITCODE
