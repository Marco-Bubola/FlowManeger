' Abre o Robô Móveis Stylo sem janela preta de terminal
Set fso = CreateObject("Scripting.FileSystemObject")
pasta = fso.GetParentFolderName(WScript.ScriptFullName)
py = pasta & "\.venv\Scripts\pythonw.exe"
If Not fso.FileExists(py) Then
  MsgBox "Rode primeiro o instalar.bat (dois cliques nele).", 48, "Robô Móveis Stylo"
  WScript.Quit
End If
Set sh = CreateObject("WScript.Shell")
sh.CurrentDirectory = pasta
sh.Run """" & py & """ """ & pasta & "\janela.pyw""", 0, False
