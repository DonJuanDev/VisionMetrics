# VisionMetrics - Deploy via WinSCP
param(
    [string]$FtpHost = "195.35.41.190",
    [string]$FtpUser = "u604248417",
    [string]$FtpPassword = "182876JJj?"
)

Write-Host "VisionMetrics - Deployment via WinSCP" -ForegroundColor Cyan
Write-Host ""

# Check if WinSCP is installed
$winscp = "C:\Program Files (x86)\WinSCP\WinSCP.com"
if (-not (Test-Path $winscp)) {
    $winscp = "C:\Program Files\WinSCP\WinSCP.com"
}

if (-not (Test-Path $winscp)) {
    Write-Host "WinSCP not found. Trying alternative method..." -ForegroundColor Yellow
    
    # Alternative: Create batch file for manual upload
    $batch = @"
@echo off
echo VisionMetrics FTP Upload Instructions
echo =======================================
echo.
echo Please use FileZilla or any FTP client with these credentials:
echo Host: $FtpHost
echo User: $FtpUser
echo Password: $FtpPassword
echo Port: 21
echo.
echo Upload ALL files from this directory to: /public_html
echo.
echo IMPORTANT FILES TO UPLOAD:
echo - env_server.txt (rename to .env on server)
echo - All backend/ folder
echo - All frontend/ folder  
echo - All src/ folder
echo - All scripts/ folder
echo - All sql/ folder
echo - All webhooks/ folder
echo - All vendor/ folder
echo - index.php
echo - composer.json
echo.
pause
"@
    
    $batch | Out-File -FilePath "UPLOAD_INSTRUCTIONS.bat" -Encoding ASCII
    
    Write-Host "Created: UPLOAD_INSTRUCTIONS.bat" -ForegroundColor Green
    Write-Host ""
    Write-Host "Please install FileZilla or WinSCP manually:" -ForegroundColor Yellow
    Write-Host "  FileZilla: https://filezilla-project.org/download.php?type=client" -ForegroundColor Cyan
    Write-Host "  WinSCP: https://winscp.net/eng/download.php" -ForegroundColor Cyan
    Write-Host ""
    
    # Try curl for small files
    Write-Host "Attempting to upload critical files via curl..." -ForegroundColor Yellow
    
    $criticalFiles = @(
        @{Local="env_server.txt"; Remote=".env"},
        @{Local="index.php"; Remote="index.php"},
        @{Local="webhooks\whatsapp.php"; Remote="webhooks/whatsapp.php"}
    )
    
    foreach ($file in $criticalFiles) {
        if (Test-Path $file.Local) {
            Write-Host "Uploading $($file.Local)..." -NoNewline
            $ftpUrl = "ftp://$FtpHost/public_html/$($file.Remote)"
            
            try {
                curl.exe -T $file.Local -u "${FtpUser}:${FtpPassword}" $ftpUrl --ftp-create-dirs 2>&1 | Out-Null
                Write-Host " OK" -ForegroundColor Green
            }
            catch {
                Write-Host " FAILED" -ForegroundColor Red
            }
        }
    }
    
    exit
}

# WinSCP script
$scriptContent = @"
option batch abort
option confirm off
open ftp://$FtpUser`:$FtpPassword@$FtpHost
option transfer binary

# Upload .env
put env_server.txt /public_html/.env

# Create directories
mkdir /public_html/logs
mkdir /public_html/uploads
mkdir /public_html/backend
mkdir /public_html/frontend
mkdir /public_html/src
mkdir /public_html/scripts
mkdir /public_html/sql
mkdir /public_html/webhooks
mkdir /public_html/vendor

# Upload files
put index.php /public_html/
put composer.json /public_html/
put composer.lock /public_html/

# Upload directories
synchronize remote -delete backend /public_html/backend
synchronize remote -delete frontend /public_html/frontend
synchronize remote -delete src /public_html/src
synchronize remote -delete scripts /public_html/scripts
synchronize remote -delete sql /public_html/sql
synchronize remote -delete webhooks /public_html/webhooks
synchronize remote vendor /public_html/vendor

close
exit
"@

$scriptFile = "winscp_upload.txt"
$scriptContent | Out-File -FilePath $scriptFile -Encoding ASCII

Write-Host "Executing WinSCP upload..." -ForegroundColor Cyan
& $winscp /script=$scriptFile /log=winscp.log

if ($LASTEXITCODE -eq 0) {
    Write-Host "SUCCESS!" -ForegroundColor Green
} else {
    Write-Host "Errors occurred. Check winscp.log" -ForegroundColor Red
}

Remove-Item $scriptFile -ErrorAction SilentlyContinue


