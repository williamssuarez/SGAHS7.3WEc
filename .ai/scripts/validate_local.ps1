<#
.SYNOPSIS
    Script determinista de validación local para SGAHS (Symfony 7.3).
.DESCRIPTION
    1. Lee los archivos modificados de '-Files' o de '## 2. Archivos Afectados' en .ai/current_spec.md.
    2. Ejecuta lint:twig en plantillas Twig modificadas.
    3. Ejecuta lint:yaml en archivos YAML modificados.
    4. Ejecuta lint:container si se tocaron archivos en src/ o config/.
    5. Compila Webpack Encore (npm run dev) si se tocaron archivos en assets/.
    6. Actualiza el esquema local Doctrine si se pasa -UpdateSchema.
#>
param(
    [string[]]$Files,
    [switch]$UpdateSchema
)

$ErrorActionPreference = "Stop"
$LocalRoot = (Get-Location).Path

if (-not $Files -or $Files.Count -eq 0) {
    $specPath = Join-Path $LocalRoot ".ai\current_spec.md"
    if (Test-Path $specPath) {
        $spec = Get-Content $specPath -Raw
        if ($spec -match "(?s)## 2\. Archivos Afectados.*?(## 3|\Z)") {
            $Files = [regex]::Matches($matches[0], '(?m)^\s*-\s*`?([a-zA-Z0-9_\-\./\\]+\.[a-zA-Z0-9\._\-]+)`?') |
                     ForEach-Object { $_.Groups[1].Value.Trim() } |
                     Where-Object { $_ -notmatch "^\.ai|^\.agent" -and $_ -ne "--" } |
                     Select-Object -Unique
        }
    }
}

$Files = $Files | Where-Object { $_ -and (Test-Path (Join-Path $LocalRoot $_)) }
if (-not $Files -or $Files.Count -eq 0) {
    Write-Host ">>> [Aviso] Sin archivos de código locales por validar." -ForegroundColor Yellow
    exit 0
}

$twigFiles   = @($Files | Where-Object { $_ -match "\.twig$" })
$yamlFiles   = @($Files | Where-Object { $_ -match "\.ya?ml$" })
$assetFiles  = @($Files | Where-Object { $_ -match "^assets[/\\]" })
$phpOrConfig = @($Files | Where-Object { $_ -match "^(src|config)[/\\]" })

foreach ($tf in $twigFiles) {
    Write-Host ">>> Validando Twig: $tf" -ForegroundColor Cyan
    php bin/console lint:twig $tf
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

foreach ($yf in $yamlFiles) {
    Write-Host ">>> Validando YAML: $yf" -ForegroundColor Cyan
    php bin/console lint:yaml $yf
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

if ($phpOrConfig.Count -gt 0) {
    Write-Host ">>> Validando contenedor de servicios Symfony..." -ForegroundColor Cyan
    php bin/console lint:container
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

if ($assetFiles.Count -gt 0) {
    Write-Host ">>> Compilando assets con Webpack Encore (npm run dev)..." -ForegroundColor Cyan
    npm run dev
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

if ($UpdateSchema) {
    Write-Host ">>> Aplicando cambios de esquema Doctrine en BD local..." -ForegroundColor Cyan
    php bin/console doctrine:schema:update --force
    if ($LASTEXITCODE -ne 0) { exit 1 }
}

Write-Host ">>> [OK] Validación local SGAHS completada exitosamente." -ForegroundColor Green
exit 0
