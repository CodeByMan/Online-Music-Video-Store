[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string] $OriginalSource,

    [Parameter(Mandatory = $false)]
    [string] $BaseUrl = "http://localhost/One%20Sound"
)

$ErrorActionPreference = "Stop"
Set-StrictMode -Version Latest

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$Php = "C:\xampp\php\php.exe"
$RestoreScript = Join-Path $PSScriptRoot "restore_private_media.php"
$TemporaryDirectory = $null

function Resolve-OriginalSource {
    param([string] $RequestedSource)

    if (-not [string]::IsNullOrWhiteSpace($RequestedSource)) {
        $Resolved = Resolve-Path -LiteralPath $RequestedSource -ErrorAction Stop
        return $Resolved.Path
    }

    $Candidates = @(
        (Join-Path (Split-Path -Parent $ProjectRoot) "One Sound(1).zip"),
        (Join-Path $ProjectRoot "One Sound(1).zip"),
        (Join-Path $env:USERPROFILE "Downloads\One Sound(1).zip"),
        (Join-Path $env:USERPROFILE "Desktop\One Sound(1).zip"),
        (Join-Path $env:USERPROFILE "Documents\One Sound(1).zip")
    )

    foreach ($Candidate in $Candidates) {
        if (Test-Path -LiteralPath $Candidate) {
            return (Resolve-Path -LiteralPath $Candidate).Path
        }
    }

    throw "Original media source was not found. Run again with -OriginalSource and provide your previous One Sound project folder or ZIP archive."
}

function Expand-MediaOnlyFromZip {
    param(
        [Parameter(Mandatory = $true)]
        [string] $ArchivePath,

        [Parameter(Mandatory = $true)]
        [string] $DestinationPath
    )

    Add-Type -AssemblyName System.IO.Compression.FileSystem

    $Archive = [System.IO.Compression.ZipFile]::OpenRead($ArchivePath)
    try {
        foreach ($Entry in $Archive.Entries) {
            $NormalizedName = $Entry.FullName.Replace("\", "/")

            if (
                $Entry.Name -eq "" -or
                $NormalizedName -notmatch "/admin/uploads/(music|videos)/[^/]+$"
            ) {
                continue
            }

            if ($Entry.Name -in @(".gitkeep", ".htaccess")) {
                continue
            }

            $RelativePath = $NormalizedName.Substring(
                $NormalizedName.IndexOf("admin/uploads/", [System.StringComparison]::OrdinalIgnoreCase)
            )

            $TargetPath = Join-Path $DestinationPath ($RelativePath.Replace("/", "\"))
            $TargetDirectory = Split-Path -Parent $TargetPath

            New-Item -ItemType Directory -Path $TargetDirectory -Force | Out-Null
            [System.IO.Compression.ZipFileExtensions]::ExtractToFile($Entry, $TargetPath, $true)
        }
    }
    finally {
        $Archive.Dispose()
    }
}

function Find-MediaDirectory {
    param(
        [Parameter(Mandatory = $true)]
        [string] $SourceRoot,

        [Parameter(Mandatory = $true)]
        [ValidateSet("music", "videos")]
        [string] $MediaType
    )

    $DirectCandidates = @(
        (Join-Path $SourceRoot "admin\uploads\$MediaType"),
        (Join-Path $SourceRoot "uploads\$MediaType"),
        (Join-Path $SourceRoot $MediaType)
    )

    foreach ($Candidate in $DirectCandidates) {
        if (Test-Path -LiteralPath $Candidate -PathType Container) {
            return (Resolve-Path -LiteralPath $Candidate).Path
        }
    }

    $Match = Get-ChildItem -LiteralPath $SourceRoot -Directory -Recurse -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Name -eq $MediaType -and
            $_.Parent -and
            $_.Parent.Name -eq "uploads" -and
            $_.Parent.Parent -and
            $_.Parent.Parent.Name -eq "admin"
        } |
        Select-Object -First 1

    if ($null -eq $Match) {
        throw "Could not locate admin\uploads\$MediaType inside $SourceRoot"
    }

    return $Match.FullName
}

function Test-PreviewStream {
    param(
        [Parameter(Mandatory = $true)]
        [ValidateSet("audio", "video")]
        [string] $Type,

        [Parameter(Mandatory = $true)]
        [string] $HomeHtml
    )

    $Pattern = "media\.php\?type=$Type&amp;id=(\d+)&amp;preview=1"
    $Match = [regex]::Match($HomeHtml, $Pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)

    if (-not $Match.Success) {
        throw "Could not find a public $Type preview URL on the homepage."
    }

    $MediaUrl = "$BaseUrl/frontend/media.php?type=$Type&id=$($Match.Groups[1].Value)&preview=1"
    $Response = Invoke-WebRequest `
        -Uri $MediaUrl `
        -Headers @{ Range = "bytes=0-1023" } `
        -UseBasicParsing `
        -TimeoutSec 30

    if ($Response.StatusCode -ne 206) {
        throw "$Type range request returned HTTP $($Response.StatusCode) instead of 206."
    }

    $ContentType = [string] $Response.Headers["Content-Type"]
    if (-not $ContentType.StartsWith("$Type/", [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "$Type response returned unexpected Content-Type: $ContentType"
    }

    Write-Host "$Type preview stream passed: HTTP 206, $ContentType" -ForegroundColor Green
}

try {
    Set-Location $ProjectRoot

    if (-not (Test-Path -LiteralPath $Php -PathType Leaf)) {
        throw "PHP was not found at $Php"
    }

    if (-not (Test-Path -LiteralPath $RestoreScript -PathType Leaf)) {
        throw "Missing restoration helper: $RestoreScript"
    }

    Write-Host "`n=== ONE SOUND PRIVATE MEDIA RESTORATION ===" -ForegroundColor Cyan

    $ResolvedSource = Resolve-OriginalSource -RequestedSource $OriginalSource
    Write-Host "Original source: $ResolvedSource" -ForegroundColor Green

    $WorkingSource = $ResolvedSource
    if (Test-Path -LiteralPath $ResolvedSource -PathType Leaf) {
        if ([System.IO.Path]::GetExtension($ResolvedSource) -ne ".zip") {
            throw "OriginalSource must be a project directory or ZIP archive."
        }

        $TemporaryDirectory = Join-Path $env:TEMP ("one-sound-media-" + [guid]::NewGuid().ToString("N"))
        New-Item -ItemType Directory -Path $TemporaryDirectory -Force | Out-Null

        Write-Host "Extracting only original audio/video files..." -ForegroundColor Cyan
        Expand-MediaOnlyFromZip -ArchivePath $ResolvedSource -DestinationPath $TemporaryDirectory
        $WorkingSource = $TemporaryDirectory
    }

    $AudioSource = Find-MediaDirectory -SourceRoot $WorkingSource -MediaType "music"
    $VideoSource = Find-MediaDirectory -SourceRoot $WorkingSource -MediaType "videos"

    Write-Host "Audio source: $AudioSource"
    Write-Host "Video source: $VideoSource"

    & $Php $RestoreScript `
        "--audio-source=$AudioSource" `
        "--video-source=$VideoSource"

    if ($LASTEXITCODE -ne 0) {
        throw "Private media restoration reported unresolved files."
    }

    Write-Host "`n=== APPLICATION CHECKS ===" -ForegroundColor Cyan

    & $Php ".\scripts\lint.php"
    if ($LASTEXITCODE -ne 0) {
        throw "PHP lint failed."
    }

    & $Php ".\scripts\check_repository.php"
    if ($LASTEXITCODE -ne 0) {
        throw "Repository verification failed."
    }

    $HomeResponse = Invoke-WebRequest `
        -Uri "$BaseUrl/frontend/home.php" `
        -UseBasicParsing `
        -TimeoutSec 30

    if ($HomeResponse.StatusCode -ne 200) {
        throw "Homepage returned HTTP $($HomeResponse.StatusCode)."
    }

    Test-PreviewStream -Type "audio" -HomeHtml $HomeResponse.Content
    Test-PreviewStream -Type "video" -HomeHtml $HomeResponse.Content

    Write-Host "`n============================================================" -ForegroundColor Green
    Write-Host "MEDIA RESTORATION COMPLETED SUCCESSFULLY" -ForegroundColor Green
    Write-Host "============================================================" -ForegroundColor Green
    Write-Host "Audio and video files are stored outside htdocs in private storage." -ForegroundColor Green
    Write-Host "No Git staging, commit, or push was performed." -ForegroundColor Yellow
}
finally {
    if ($null -ne $TemporaryDirectory -and (Test-Path -LiteralPath $TemporaryDirectory)) {
        Remove-Item -LiteralPath $TemporaryDirectory -Recurse -Force -ErrorAction SilentlyContinue
    }
}
