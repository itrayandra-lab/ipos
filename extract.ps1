Add-Type -AssemblyName System.IO.Compression.FileSystem
$filePath = 'D:\Iman Cangga\Herd\pos-beautylatory\dokumen_support\Blueprint_iPOS_RayCorp_untuk_IT.docx'
if (Test-Path $filePath) {
    $zip = [System.IO.Compression.ZipFile]::OpenRead($filePath)
    $entry = $zip.GetEntry('word/document.xml')
    $stream = $entry.Open()
    $reader = New-Object IO.StreamReader($stream)
    $text = $reader.ReadToEnd()
    $reader.Close()
    $stream.Close()
    $zip.Dispose()

    $matches = [regex]::Matches($text, '<w:t[^>]*>(.*?)</w:t>')
    $result = ''
    foreach ($m in $matches) {
        if ($m.Groups[1].Value) {
            $result += $m.Groups[1].Value + " "
        }
    }
    Write-Output $result
} else {
    Write-Output "File not found"
}
