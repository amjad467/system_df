Add-Type -AssemblyName System.Drawing

$iconsDir = "public/icons"
if (-not (Test-Path $iconsDir)) {
    New-Item -ItemType Directory -Path $iconsDir -Force | Out-Null
}

$logoPath = "public/images/krg_logo.png"
$fullLogoPath = (Resolve-Path $logoPath).Path
$krgLogo = [System.Drawing.Image]::FromFile($fullLogoPath)

function Generate-PwaIcon {
    param(
        [int]$size,
        [string]$outputPath,
        [bool]$isMaskable = $false
    )

    $bmp = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
    $g = [System.Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality

    $cTop = [System.Drawing.Color]::FromArgb(255, 12, 25, 44)
    $cBottom = [System.Drawing.Color]::FromArgb(255, 3, 105, 161)
    $rect = New-Object System.Drawing.Rectangle(0, 0, $size, $size)
    $brush = New-Object System.Drawing.Drawing2D.LinearGradientBrush($rect, $cTop, $cBottom, 45.0)

    if ($isMaskable) {
        $g.FillRectangle($brush, $rect)
    } else {
        $cornerRad = [int]($size * 0.22)
        $path = New-Object System.Drawing.Drawing2D.GraphicsPath
        $d = $cornerRad * 2
        $dim = [int]($size - 1)
        $r = New-Object System.Drawing.Rectangle(0, 0, $dim, $dim)
        $path.AddArc($r.X, $r.Y, $d, $d, 180, 90)
        $path.AddArc($r.X + $r.Width - $d, $r.Y, $d, $d, 270, 90)
        $path.AddArc($r.X + $r.Width - $d, $r.Y + $r.Height - $d, $d, $d, 0, 90)
        $path.AddArc($r.X, $r.Y + $r.Height - $d, $d, $d, 90, 90)
        $path.CloseFigure()
        
        $g.FillPath($brush, $path)

        $goldPen = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(180, 245, 158, 11), [float]([Math]::Max(2, $size * 0.02)))
        $g.DrawPath($goldPen, $path)
        $goldPen.Dispose()
        $path.Dispose()
    }
    $brush.Dispose()

    $glowRect = New-Object System.Drawing.Rectangle([int]($size * 0.15), [int]($size * 0.15), [int]($size * 0.7), [int]($size * 0.7))
    $glowBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(40, 56, 189, 248))
    $g.FillEllipse($glowBrush, $glowRect)
    $glowBrush.Dispose()

    $logoRatio = $krgLogo.Width / $krgLogo.Height
    $targetHeight = if ($isMaskable) { [int]($size * 0.60) } else { [int]($size * 0.72) }
    $targetWidth = [int]($targetHeight * $logoRatio)
    $destX = [int](($size - $targetWidth) / 2)
    $destY = [int](($size - $targetHeight) / 2)

    $g.DrawImage($krgLogo, $destX, $destY, $targetWidth, $targetHeight)

    $g.Dispose()
    $bmp.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $bmp.Dispose()
    Write-Host "Generated: $outputPath"
}

Generate-PwaIcon -size 512 -outputPath "public/icons/icon-512x512.png"
Generate-PwaIcon -size 192 -outputPath "public/icons/icon-192x192.png"
Generate-PwaIcon -size 180 -outputPath "public/icons/apple-touch-icon.png"
Generate-PwaIcon -size 512 -outputPath "public/icons/icon-maskable-512x512.png" -isMaskable $true
Generate-PwaIcon -size 192 -outputPath "public/icons/icon-maskable-192x192.png" -isMaskable $true
Generate-PwaIcon -size 32 -outputPath "public/icons/favicon-32x32.png"

$krgLogo.Dispose()
Write-Host "All PWA Icons created successfully!"
