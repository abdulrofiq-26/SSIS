$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)

$services = @(
    @{
        Name = 'Python ResNet service'
        File = Join-Path (Get-Location) 'venv-tf\Scripts\python.exe'
        Arguments = @('scripts\resnet_service.py')
    },
    @{
        Name = 'Laravel queue worker'
        File = (Get-Command php -ErrorAction Stop).Source
        Arguments = @('artisan', 'queue:work', '--tries=3', '--timeout=300')
    },
    @{
        Name = 'Vite development server'
        File = (Get-Command node -ErrorAction Stop).Source
        Arguments = @('node_modules/vite/bin/vite.js', '--host', '127.0.0.1')
    },
    @{
        Name = 'Laravel web server'
        File = (Get-Command php -ErrorAction Stop).Source
        Arguments = @('artisan', 'serve', '--host=127.0.0.1', '--port=8000')
    }
)

$processes = @()

try {
    foreach ($service in $services) {
        if ($service.Name -eq 'Python ResNet service') {
            if (-not (Test-Path $service.File)) {
                throw "Python virtual environment not found: $($service.File)"
            }
        }
        elseif (-not (Test-Path $service.File)) {
            throw "Required executable not found: $($service.File)"
        }

        Write-Host "[SSIS] Starting $($service.Name)..." -ForegroundColor Cyan
        $process = Start-Process -FilePath $service.File `
            -ArgumentList ($service.Arguments -join ' ') `
            -WorkingDirectory (Get-Location).Path `
            -NoNewWindow `
            -PassThru
        $processes += [PSCustomObject]@{
            Name = $service.Name
            Process = $process
        }
    }

    Write-Host '[SSIS] Waiting for Python ResNet service (up to 180 seconds)...' -ForegroundColor Yellow
    $resnetReady = $false
    for ($i = 0; $i -lt 180; $i++) {
        $pythonProcess = $processes | Where-Object { $_.Name -eq 'Python ResNet service' } | Select-Object -First 1
        if ($pythonProcess.Process.HasExited) {
            throw 'Python ResNet service exited before becoming ready. Check Python dependencies and service output above.'
        }

        try {
            $health = Invoke-RestMethod -Uri 'http://127.0.0.1:5005/health' -TimeoutSec 2
            if ($health.status -eq 'ok') {
                $resnetReady = $true
                Write-Host "[SSIS] Python service ready; indexed vectors: $($health.indexed)" -ForegroundColor Green
                break
            }
        }
        catch {
            Start-Sleep -Seconds 1
        }
    }

    if (-not $resnetReady) {
        throw 'Python service did not become ready within 180 seconds.'
    }

    Write-Host '[SSIS] All services are running. Open http://127.0.0.1:8000' -ForegroundColor Green
    Write-Host '[SSIS] Keep this terminal open. Press Ctrl+C to stop all services.' -ForegroundColor Yellow
    while ($true) {
        foreach ($entry in $processes) {
            $entry.Process.Refresh()
            if ($entry.Process.HasExited) {
                throw "$($entry.Name) exited unexpectedly with code $($entry.Process.ExitCode)."
            }
        }
        Start-Sleep -Seconds 2
    }
}
finally {
    Write-Host '[SSIS] Stopping started services...' -ForegroundColor Yellow
    foreach ($entry in $processes) {
        try {
            $entry.Process.Refresh()
            if (-not $entry.Process.HasExited) {
                Stop-Process -Id $entry.Process.Id -Force -ErrorAction SilentlyContinue
            }
        }
        catch {
            # Ignore processes that already stopped.
        }
    }
}
