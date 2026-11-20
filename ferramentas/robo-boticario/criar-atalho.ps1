# Cria o atalho "Robô Boticário" na Área de Trabalho (abre sem terminal; dá para arrastar a nota em cima dele)
$pasta = $PSScriptRoot
$desk = [Environment]::GetFolderPath('Desktop')
$sh = New-Object -ComObject WScript.Shell
$lnk = $sh.CreateShortcut((Join-Path $desk 'Robô Boticário.lnk'))
$lnk.TargetPath = Join-Path $pasta '.venv\Scripts\pythonw.exe'
$lnk.Arguments = '"' + (Join-Path $pasta 'app.pyw') + '"'
$lnk.WorkingDirectory = $pasta
$lnk.IconLocation = (Join-Path $pasta 'icone.ico') + ',0'
$lnk.Description = 'Robô de fotos e códigos de barras do FlowManeger'
$lnk.Save()
Write-Host "Atalho criado em $desk"
