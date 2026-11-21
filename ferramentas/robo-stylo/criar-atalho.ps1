# Cria o atalho "Robô Móveis Stylo" (com o ícone) na Área de Trabalho e no menu Iniciar. Abre sem terminal.
$pasta = $PSScriptRoot
$icone = Join-Path $pasta 'icone.ico'
$sh = New-Object -ComObject WScript.Shell
$lugares = @([Environment]::GetFolderPath('Desktop'), (Join-Path ([Environment]::GetFolderPath('Programs')) ''))
foreach ($lugar in $lugares) {
  if (-not $lugar -or -not (Test-Path $lugar)) { continue }
  $lnk = $sh.CreateShortcut((Join-Path $lugar 'Robô Móveis Stylo.lnk'))
  $lnk.TargetPath = Join-Path $pasta '.venv\Scripts\pythonw.exe'
  $lnk.Arguments = '"' + (Join-Path $pasta 'janela.pyw') + '"'
  $lnk.WorkingDirectory = $pasta
  $lnk.IconLocation = $icone + ',0'
  $lnk.Description = 'Robô de cadastro da Móveis Stylo'
  $lnk.Save()
  Write-Host "Atalho criado em $lugar"
}
# Faz o Windows redesenhar os ícones (senão às vezes continua o ícone branco antigo)
try { & ie4uinit.exe -show } catch {}
