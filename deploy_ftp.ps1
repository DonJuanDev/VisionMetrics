# ═══════════════════════════════════════════════════════════════════════
# VisionMetrics - FTP Upload Script (Hostinger Deployment)
# ═══════════════════════════════════════════════════════════════════════
# Purpose: Upload complete application to Hostinger via FTP
# Branch: feature/hostapp-whatsapp-qr
# ═══════════════════════════════════════════════════════════════════════

param(
    [string]$FtpHost = "195.35.41.190",
    [int]$FtpPort = 21,
    [string]$FtpUser = "u604248417",
    [string]$FtpPassword = "182876JJj?",
    [string]$RemotePath = "/public_html",
    [string]$LocalPath = ".",
    [switch]$SkipVendor = $false,
    [switch]$Verbose = $false
)

$ErrorActionPreference = "Continue"
$ProgressPreference = "SilentlyContinue"

Write-Host "═══════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  VisionMetrics - FTP Deployment to Hostinger" -ForegroundColor Cyan
Write-Host "  Branch: feature/hostapp-whatsapp-qr" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# ═══════════════════════════════════════════════════════════════════════
# Configuration
# ═══════════════════════════════════════════════════════════════════════
$FtpUri = "ftp://$FtpHost`:$FtpPort"
$DeployLog = "deploy_log_$(Get-Date -Format 'yyyyMMdd_HHmmss').txt"
$UploadCount = 0
$ErrorCount = 0
$StartTime = Get-Date

# Files and directories to exclude
$ExcludePatterns = @(
    "*.git*",
    "*.git",
    "node_modules",
    ".vscode",
    ".idea",
    "*.tmp",
    "*.log",
    "deploy_*.ps1",
    "generate_key.ps1",
    ".env.production",
    "deploy_log_*.txt",
    "deprecated",
    "tests",
    "*.md",
    "*.txt",
    "phpunit.xml",
    "Makefile",
    ".htaccess.bak"
)

if ($SkipVendor) {
    $ExcludePatterns += "vendor"
}

# ═══════════════════════════════════════════════════════════════════════
# Helper Functions
# ═══════════════════════════════════════════════════════════════════════

function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    
    # Color based on level
    switch ($Level) {
        "ERROR"   { Write-Host $logEntry -ForegroundColor Red }
        "SUCCESS" { Write-Host $logEntry -ForegroundColor Green }
        "WARNING" { Write-Host $logEntry -ForegroundColor Yellow }
        default   { if ($Verbose) { Write-Host $logEntry -ForegroundColor Gray } }
    }
    
    Add-Content -Path $DeployLog -Value $logEntry
}

function Should-Exclude {
    param([string]$Path)
    
    foreach ($pattern in $ExcludePatterns) {
        if ($Path -like "*$pattern*") {
            return $true
        }
    }
    return $false
}

function Create-FtpDirectory {
    param([string]$RemoteDir)
    
    try {
        $uri = "$FtpUri$RemoteDir"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        
        $response = $request.GetResponse()
        $response.Close()
        
        Write-Log "Created directory: $RemoteDir" "INFO"
        return $true
    }
    catch {
        # Directory might already exist - that's OK
        if ($_.Exception.InnerException.Response.StatusCode -ne [System.Net.FtpStatusCode]::ActionNotTakenFileUnavailable) {
            Write-Log "Error creating directory $RemoteDir : $($_.Exception.Message)" "WARNING"
        }
        return $false
    }
}

function Upload-File {
    param(
        [string]$LocalFile,
        [string]$RemoteFile
    )
    
    try {
        $uri = "$FtpUri$RemoteFile"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $request.UseBinary = $true
        $request.UsePassive = $true
        $request.KeepAlive = $false
        
        # Read file
        $fileContent = [System.IO.File]::ReadAllBytes($LocalFile)
        $request.ContentLength = $fileContent.Length
        
        # Upload
        $requestStream = $request.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()
        
        # Get response
        $response = $request.GetResponse()
        $response.Close()
        
        $script:UploadCount++
        Write-Log "Uploaded: $RemoteFile" "INFO"
        return $true
    }
    catch {
        $script:ErrorCount++
        Write-Log "Failed to upload $RemoteFile : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

function Upload-Directory {
    param(
        [string]$LocalDir,
        [string]$RemoteDir
    )
    
    # Create remote directory
    Create-FtpDirectory -RemoteDir $RemoteDir
    
    # Get all items in local directory
    $items = Get-ChildItem -Path $LocalDir -Force
    
    foreach ($item in $items) {
        $relativePath = $item.FullName.Substring($LocalPath.Length).Replace('\', '/')
        
        # Check if should exclude
        if (Should-Exclude -Path $relativePath) {
            Write-Log "Skipping (excluded): $relativePath" "WARNING"
            continue
        }
        
        $remoteItemPath = "$RemoteDir/$($item.Name)"
        
        if ($item.PSIsContainer) {
            # Recursive directory upload
            Upload-Directory -LocalDir $item.FullName -RemoteDir $remoteItemPath
        }
        else {
            # Upload file
            Upload-File -LocalFile $item.FullName -RemoteFile $remoteItemPath
        }
    }
}

# ═══════════════════════════════════════════════════════════════════════
# Main Execution
# ═══════════════════════════════════════════════════════════════════════

Write-Log "Starting FTP deployment..." "INFO"
Write-Log "Local path: $LocalPath" "INFO"
Write-Log "Remote path: $FtpUri$RemotePath" "INFO"
Write-Log "Skip vendor: $SkipVendor" "INFO"
Write-Host ""

# Step 1: Upload .env file first
Write-Host "[1/4] Uploading .env configuration..." -ForegroundColor Cyan
if (Test-Path ".env.production") {
    Upload-File -LocalFile ".env.production" -RemoteFile "$RemotePath/.env"
    Write-Log "Uploaded .env file" "SUCCESS"
} else {
    Write-Log ".env.production not found - skipping" "WARNING"
}
Write-Host ""

# Step 2: Create essential directories
Write-Host "[2/4] Creating directory structure..." -ForegroundColor Cyan
$essentialDirs = @(
    "$RemotePath/logs",
    "$RemotePath/uploads",
    "$RemotePath/backend",
    "$RemotePath/frontend",
    "$RemotePath/src",
    "$RemotePath/scripts",
    "$RemotePath/sql",
    "$RemotePath/webhooks",
    "$RemotePath/vendor"
)

foreach ($dir in $essentialDirs) {
    Create-FtpDirectory -RemoteDir $dir
}
Write-Host ""

# Step 3: Upload all files recursively
Write-Host "[3/4] Uploading application files..." -ForegroundColor Cyan
Write-Host "This may take several minutes depending on your connection..." -ForegroundColor Yellow
Write-Host ""

$rootItems = Get-ChildItem -Path $LocalPath -Force

foreach ($item in $rootItems) {
    $relativePath = $item.Name
    
    # Check exclusions
    if (Should-Exclude -Path $relativePath) {
        Write-Host "Skipping: $relativePath" -ForegroundColor DarkGray
        continue
    }
    
    $remoteItemPath = "$RemotePath/$relativePath"
    
    if ($item.PSIsContainer) {
        Write-Host "Uploading directory: $relativePath ..." -ForegroundColor Yellow
        Upload-Directory -LocalDir $item.FullName -RemoteDir $remoteItemPath
    }
    else {
        Upload-File -LocalFile $item.FullName -RemoteFile $remoteItemPath
    }
}

Write-Host ""

# Step 4: Verify critical files
Write-Host "[4/4] Verifying deployment..." -ForegroundColor Cyan

$criticalFiles = @(
    "$RemotePath/index.php",
    "$RemotePath/.env",
    "$RemotePath/backend/login.php",
    "$RemotePath/webhooks/whatsapp.php",
    "$RemotePath/sql/migrations/20251007_whatsapp_sessions_and_conversations.sql"
)

$verifyCount = 0
foreach ($file in $criticalFiles) {
    try {
        $uri = "$FtpUri$file"
        $request = [System.Net.FtpWebRequest]::Create($uri)
        $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $request.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $request.UseBinary = $true
        $request.UsePassive = $true
        
        $response = $request.GetResponse()
        $size = $response.ContentLength
        $response.Close()
        
        Write-Host "  [OK] $file ($size bytes)" -ForegroundColor Green
        $verifyCount++
    }
    catch {
        Write-Host "  [ERROR] $file (NOT FOUND)" -ForegroundColor Red
    }
}

Write-Host ""

# ═══════════════════════════════════════════════════════════════════════
# Summary Report
# ═══════════════════════════════════════════════════════════════════════

$EndTime = Get-Date
$Duration = $EndTime - $StartTime

Write-Host "═══════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  DEPLOYMENT SUMMARY" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Files uploaded:     $UploadCount" -ForegroundColor Green
Write-Host "  Errors:             $ErrorCount" -ForegroundColor $(if ($ErrorCount -eq 0) { "Green" } else { "Red" })
Write-Host "  Files verified:     $verifyCount / $($criticalFiles.Count)" -ForegroundColor $(if ($verifyCount -eq $criticalFiles.Count) { "Green" } else { "Yellow" })
Write-Host "  Duration:           $($Duration.ToString('mm\:ss'))" -ForegroundColor Cyan
Write-Host "  Log file:           $DeployLog" -ForegroundColor Cyan
Write-Host ""

if ($ErrorCount -eq 0 -and $verifyCount -eq $criticalFiles.Count) {
    Write-Host "  STATUS: DEPLOYMENT SUCCESSFUL!" -ForegroundColor Green
} elseif ($ErrorCount -gt 0) {
    Write-Host "  STATUS: DEPLOYMENT COMPLETED WITH ERRORS" -ForegroundColor Yellow
} else {
    Write-Host "  STATUS: DEPLOYMENT COMPLETED (verify warnings)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

Write-Log "Deployment completed. Uploaded: $UploadCount files, Errors: $ErrorCount" "SUCCESS"

