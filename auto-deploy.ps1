# Auto deploy: watches the project and, after you save changes, rebuilds docs/
# (php build-static.php), commits and pushes to GitHub, so GitHub Pages updates by itself.
#
# Run:  powershell -ExecutionPolicy Bypass -File auto-deploy.ps1
# Stop: Ctrl + C (or close the terminal)
#
# config.php is never pushed (it is in .gitignore).

param(
  [int]$QuietSeconds = 8,     # wait until no file has changed for this long before deploying
  [int]$PollSeconds  = 3,
  [switch]$Once               # deploy once now and exit
)

$ErrorActionPreference = 'Continue'
Set-Location -LiteralPath $PSScriptRoot

$git = (Get-Command git -ErrorAction SilentlyContinue).Source
if (-not $git) { $git = 'C:\Program Files\Git\cmd\git.exe' }
if (-not (Test-Path $git)) { Write-Host 'Git not found. Install Git first.' -ForegroundColor Red; exit 1 }
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { Write-Host 'PHP not found on PATH.' -ForegroundColor Red; exit 1 }

function Write-Log($msg, $color = 'Gray') { Write-Host ("[{0}] {1}" -f (Get-Date -Format 'HH:mm:ss'), $msg) -ForegroundColor $color }

# Latest write time of the files we care about (ignores .git, docs/ build output and data/)
function Get-Stamp {
  $files = Get-ChildItem -LiteralPath $PSScriptRoot -Recurse -File -Force -ErrorAction SilentlyContinue |
    Where-Object { $_.FullName -notmatch '\\(\.git|docs|data|\.vscode)(\\|$)' }
  if (-not $files) { return 0 }
  ($files | Measure-Object -Property LastWriteTimeUtc -Maximum).Maximum.Ticks
}

function Invoke-Deploy {
  Write-Log 'Change detected. Building static site...' 'Cyan'
  $build = & php build-static.php 2>&1
  if ($LASTEXITCODE -ne 0) {
    Write-Log 'Build FAILED, nothing was pushed. Fix the error and save again:' 'Red'
    $build | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
    return
  }
  # Quick PHP syntax check of every PHP file before publishing
  foreach ($f in Get-ChildItem -LiteralPath $PSScriptRoot -Filter *.php -File) {
    $lint = & php -l $f.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { Write-Log "PHP error in $($f.Name), nothing was pushed:" 'Red'; $lint | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }; return }
  }

  & $git add -A 2>&1 | Out-Null
  $changes = & $git status --porcelain
  if (-not $changes) { Write-Log 'No real changes to publish.'; return }

  # File names with spaces come back quoted ("a b.jpg"). Strip the quotes for the message,
  # and pass the message through a file so special characters can't break the git command.
  $names = ($changes | ForEach-Object { $_.Substring(3).Trim('"') } | Select-Object -First 5) -join ', '
  $msgFile = Join-Path ([IO.Path]::GetTempPath()) 'xgtech-autodeploy-msg.txt'
  [IO.File]::WriteAllText($msgFile, "Auto update: $names", (New-Object Text.UTF8Encoding $false))
  $commitOut = & $git commit -q -F $msgFile 2>&1
  Remove-Item $msgFile -ErrorAction SilentlyContinue
  if ($LASTEXITCODE -ne 0) {
    Write-Log 'Commit FAILED, nothing was pushed:' 'Red'
    $commitOut | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
    return
  }
  Write-Log "Committed: $names"

  $env:GCM_INTERACTIVE = 'auto'
  $push = & $git push origin main 2>&1
  if ($LASTEXITCODE -eq 0) {
    Write-Log 'Pushed. Live site updates in about 1 minute: https://shorifa1234.github.io/xgone-landing-page/' 'Green'
  } else {
    Write-Log 'Push FAILED (the change is committed locally and will be pushed with the next save):' 'Red'
    $push | ForEach-Object { Write-Host "    $_" -ForegroundColor Red }
  }
}

if ($Once) { Invoke-Deploy; exit }

Write-Log 'Auto deploy is running. Save a file to publish it. Press Ctrl+C to stop.' 'Green'
$last = Get-Stamp
$changedAt = $null
while ($true) {
  Start-Sleep -Seconds $PollSeconds
  $now = Get-Stamp
  if ($now -ne $last) { $last = $now; $changedAt = Get-Date; continue }
  if ($changedAt -and ((Get-Date) - $changedAt).TotalSeconds -ge $QuietSeconds) {
    $changedAt = $null
    Invoke-Deploy
    $last = Get-Stamp
  }
}
