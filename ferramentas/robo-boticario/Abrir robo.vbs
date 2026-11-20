' Abre o Robo Boticario sem janela preta de terminal
Set fso = CreateObject("Scripting.FileSystemObject")
pasta = fso.GetParentFolderName(WScript.ScriptFullName)
py = pasta & "\.venv\Scripts\pythonw.exe"
If Not fso.FileExists(py) Then
  MsgBox "Rode primeiro o instalar.bat (dois cliques nele).", 48, "Robo Boticario"
  WScript.Quit
End If
args = ""
For Each a In WScript.Arguments
  args = args & " """ & a & """"
Next
Set sh = CreateObject("WScript.Shell")
sh.CurrentDirectory = pasta
sh.Run """" & py & """ """ & pasta & "\app.pyw""" & args, 0, False
