# Cria o atalho "Robô Móveis Stylo" na Área de Trabalho (abre sem terminal)
$pasta = $PSScriptRoot
$desk = [Environment]::GetFolderPath('Desktop')
$sh = New-Object -ComObject WScript.Shell
$lnk = $sh.CreateShortcut((Join-Path $desk 'Robô Móveis Stylo.lnk'))
$lnk.TargetPath = Join-Path $pasta '.venv\Scripts\pythonw.exe'
$lnk.Arguments = '"' + (Join-Path $pasta 'janela.pyw') + '"'
$lnk.WorkingDirectory = $pasta
$lnk.IconLocation = (Join-Path $pasta 'icone.ico') + ',0'
$lnk.Description = 'Robô de cadastro da Móveis Stylo'
$lnk.Save()
Write-Host "Atalho criado em $desk"
