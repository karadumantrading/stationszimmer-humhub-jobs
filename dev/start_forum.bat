@echo off
REM ============================================================
REM  Stationszimmer-Forum (HumHub via Docker) starten + oeffnen
REM  Liegt in dev\ neben docker-compose.yml. Doppelklick genuegt.
REM ============================================================
cd /d "%~dp0"

set "URL=http://localhost:8080/index.php?r=forum/category/index"

echo.
echo  Stationszimmer-Forum wird gestartet ...
echo.

REM 1) Docker-Engine erreichbar? Sonst Docker Desktop starten und warten.
docker version >nul 2>&1
if errorlevel 1 (
  echo  - Docker-Engine nicht erreichbar, starte Docker Desktop ...
  start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
  echo  - Warte auf die Docker-Engine ^(bis zu 120s^) ...
  powershell -NoProfile -Command "$ok=$false; for($i=0;$i -lt 60;$i++){ docker version *> $null; if($LASTEXITCODE -eq 0){$ok=$true; break}; Start-Sleep 2 }; if(-not $ok){ exit 1 }"
  if errorlevel 1 (
    echo  [!] Docker-Engine kam nicht hoch. Bitte Docker Desktop manuell starten und erneut ausfuehren.
    pause
    exit /b 1
  )
)

REM 2) Stack hochfahren (idempotent - startet nur, was noch nicht laeuft).
echo  - Container hochfahren ...
docker compose up -d
if errorlevel 1 (
  echo  [!] 'docker compose up' fehlgeschlagen.
  pause
  exit /b 1
)

REM 3) Auf HumHub warten (Abbruch, sobald der Server irgendeine Antwort liefert).
echo  - Warte auf HumHub ...
powershell -NoProfile -Command "for($i=0;$i -lt 40;$i++){ try{ Invoke-WebRequest -UseBasicParsing -TimeoutSec 3 '%URL%' ^| Out-Null; break }catch{ if($_.Exception.Response){ break } }; Start-Sleep 2 }"

REM 4) Forum im Standardbrowser oeffnen.
echo  - Oeffne Forum: %URL%
start "" "%URL%"

echo.
echo  Fertig. (Login: admin / stationszimmer1)
echo.
