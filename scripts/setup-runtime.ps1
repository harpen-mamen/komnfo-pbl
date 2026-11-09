$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path $PSScriptRoot -Parent
$runtimeDir = Join-Path $projectRoot '.runtime'
$downloadDir = Join-Path $runtimeDir 'downloads'
$phpDir = Join-Path $runtimeDir 'php85'
New-Item -ItemType Directory -Force -Path $downloadDir | Out-Null

if (-not (Test-Path (Join-Path $phpDir 'php.exe'))) {
    $archive = Join-Path $downloadDir 'php-8.5.12-nts-Win32-vs17-x64.zip'
    Invoke-WebRequest -UseBasicParsing -Uri 'https://downloads.php.net/~windows/releases/php-8.5.12-nts-Win32-vs17-x64.zip' -OutFile $archive
    $expectedHash = '2ab242af38d76bdf417cde5ec048b1bcd76a5a40f39a3d15d5f046f38571c477'
    if ((Get-FileHash -LiteralPath $archive -Algorithm SHA256).Hash.ToLowerInvariant() -ne $expectedHash) {
        throw 'Checksum PHP tidak cocok; instalasi dihentikan.'
    }
    Expand-Archive -LiteralPath $archive -DestinationPath $phpDir -Force
}

$composerFile = Join-Path $runtimeDir 'composer.phar'
if (-not (Test-Path $composerFile)) {
    Invoke-WebRequest -UseBasicParsing -Uri 'https://getcomposer.org/download/2.10.3/composer.phar' -OutFile $composerFile
    $expectedHash = '7a2d379d5b8ffdaa028580ef26494c36d2feef4b178d3dd1473a4dbc5e17c8d6'
    if ((Get-FileHash -LiteralPath $composerFile -Algorithm SHA256).Hash.ToLowerInvariant() -ne $expectedHash) {
        throw 'Checksum Composer tidak cocok; instalasi dihentikan.'
    }
}

& (Join-Path $projectRoot 'php.cmd') -v
if ($LASTEXITCODE -ne 0) { throw 'PHP gagal dijalankan. Periksa Microsoft Visual C++ Redistributable x64.' }
& (Join-Path $projectRoot 'composer.cmd') --version
if ($LASTEXITCODE -ne 0) { throw 'Composer gagal dijalankan.' }
Write-Host 'Runtime siap. Gunakan .\php.cmd artisan ... dan .\composer.cmd ... dari root proyek.'
