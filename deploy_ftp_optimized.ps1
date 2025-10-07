# ═══════════════════════════════════════════════════════════════════════
# VisionMetrics - FTP Upload Script (Hostinger Deployment) - OPTIMIZED
# ═══════════════════════════════════════════════════════════════════════

param(
    [string]$FtpHost = "195.35.41.190",
    [int]$FtpPort = 21,
    [string]$FtpUser = "u604248417",
    [string]$FtpPassword = "182876JJj?",
    [string]$RemotePath = "/public_html"
)

$ErrorActionPreference = "Continue"
$ProgressPreference = "SilentlyContinue"

Write-Host "=" -NoNewline -ForegroundColor Cyan
Write-Host "VisionMetrics - FTP Deployment" -ForegroundColor Cyan
Write-Host "Target: $FtpHost$RemotePath" -ForegroundColor Cyan
Write-Host ""

$FtpUri = "ftp://$FtpHost`:$FtpPort"
$UploadCount = 0
$ErrorCount = 0

# Files/dirs to skip
$Skip = @("*.git*", "node_modules", ".vscode", ".idea", "*.tmp", "*.log", "deploy_*.ps1", "generate_key.ps1", "deprecated", "tests", "*.md", "*.txt", "phpunit.xml", "Makefile")

function Should-Skip {
    param([string]$Path)
    foreach ($s in $Skip) {
        if ($Path -like "*$s*") { return $true }
    }
    return $false
}

function FTP-Upload {
    param([string]$LocalFile, [string]$RemoteFile)
    
    try {
        $uri = "$FtpUri$RemoteFile"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        $request.Timeout = 30000
        
        $fileBytes = [System.IO.File]::ReadAllBytes($LocalFile)
        $request.ContentLength = $fileBytes.Length
        
        $stream = $request.GetRequestStream()
        $stream.Write($fileBytes, 0, $fileBytes.Length)
        $stream.Close()
        
        $response = $request.GetResponse()
        $response.Close()
        
        $script:UploadCount++
        Write-Host "." -NoNewline -ForegroundColor Green
        return $true
    }
    catch {
        $script:ErrorCount++
        Write-Host "X" -NoNewline -ForegroundColor Red
        return $false
    }
}

function FTP-MkDir {
    param([string]$RemoteDir)
    
    try {
        $uri = "$FtpUri$RemoteDir"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        $request.Timeout = 10000
        
        $response = $request.GetResponse()
        $response.Close()
        return $true
    }
    catch {
        return $false
    }
}

function Upload-Dir {
    param([string]$LocalDir, [string]$RemoteDir)
    
    FTP-MkDir -RemoteDir $RemoteDir | Out-Null
    
    $items = Get-ChildItem -Path $LocalDir -Force -ErrorAction SilentlyContinue
    
    foreach ($item in $items) {
        if (Should-Skip -Path $item.Name) { continue }
        
        $remotePath = "$RemoteDir/$($item.Name)"
        
        if ($item.PSIsContainer) {
            Upload-Dir -LocalDir $item.FullName -RemoteDir $remotePath
        }
        else {
            FTP-Upload -LocalFile $item.FullName -RemoteFile $remotePath | Out-Null
        }
    }
}

# ═══════════════════════════════════════════════════════════════════════
# MAIN
# ═══════════════════════════════════════════════════════════════════════

Write-Host "[1/5] Uploading .env..." -ForegroundColor Cyan
if (Test-Path "env_server.txt") {
    FTP-Upload -LocalFile "env_server.txt" -RemoteFile "$RemotePath/.env" | Out-Null
    Write-Host " DONE" -ForegroundColor Green
}

Write-Host "[2/5] Creating directories..." -ForegroundColor Cyan
@("$RemotePath/logs", "$RemotePath/uploads", "$RemotePath/backend", "$RemotePath/frontend", "$RemotePath/src", "$RemotePath/scripts", "$RemotePath/sql", "$RemotePath/webhooks", "$RemotePath/vendor") | ForEach-Object {
    FTP-MkDir -RemoteDir $_ | Out-Null
}
Write-Host " DONE" -ForegroundColor Green

Write-Host "[3/5] Uploading core files..." -ForegroundColor Cyan
@("index.php", "composer.json", "composer.lock") | ForEach-Object {
    if (Test-Path $_) {
        FTP-Upload -LocalFile $_ -RemoteFile "$RemotePath/$_" | Out-Null
    }
}
Write-Host " DONE" -ForegroundColor Green

Write-Host "[4/5] Uploading directories (this may take time)..." -ForegroundColor Cyan
Write-Host "Progress: " -NoNewline
@("backend", "frontend", "src", "scripts", "sql", "webhooks", "vendor", "mercadopago", "worker") | ForEach-Object {
    if (Test-Path $_) {
        Upload-Dir -LocalDir $_ -RemoteDir "$RemotePath/$_"
    }
}
Write-Host ""
Write-Host " DONE" -ForegroundColor Green

Write-Host "[5/5] Verifying..." -ForegroundColor Cyan
$critical = @(
    "$RemotePath/index.php",
    "$RemotePath/.env",
    "$RemotePath/backend/login.php",
    "$RemotePath/webhooks/whatsapp.php",
    "$RemotePath/sql/migrations/20251007_whatsapp_sessions_and_conversations.sql"
)

$verified = 0
foreach ($file in $critical) {
    try {
        $uri = "$FtpUri$file"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.Timeout = 10000
        
        $response = $request.GetResponse()
        $response.Close()
        $verified++
    }
    catch {
        Write-Host "  Missing: $file" -ForegroundColor Red
    }
}
Write-Host " $verified/$($critical.Count) files verified" -ForegroundColor Green

Write-Host ""
Write-Host "=" -NoNewline -ForegroundColor Cyan
Write-Host "DEPLOYMENT SUMMARY" -ForegroundColor Cyan
Write-Host "Files uploaded: $UploadCount" -ForegroundColor Green
Write-Host "Errors: $ErrorCount" -ForegroundColor $(if ($ErrorCount -eq 0) { "Green" } else { "Red" })
Write-Host "Status: " -NoNewline
if ($verified -eq $critical.Count -and $ErrorCount -eq 0) {
    Write-Host "SUCCESS" -ForegroundColor Green
} else {
    Write-Host "PARTIAL (check errors)" -ForegroundColor Yellow
}
Write-Host ""


