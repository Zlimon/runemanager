Set objFso = CreateObject("Scripting.FileSystemObject")
Set Folder = objFSO.GetFolder("D:\laragon\www\runemanager\public\images\boss\")

For Each File In Folder.Files
    sNewFile = File.Name
    sNewFile = Replace(sNewFile,"_","-")
    if (sNewFile<>File.Name) then 
        File.Move(File.ParentFolder+"\"+sNewFile)
    end if

Next