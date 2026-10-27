<#
.SYNOPSIS
	Rebuilds every raster icon from assets/icon-master.png.

.DESCRIPTION
	assets/icon-master.png is a 1536 px render of assets/icon.svg (3× the
	largest icon). Each size below is an exact integer downscale, so the
	antialiasing stays clean.

	The banners and the social preview are NOT built here: they use the real
	webfonts and are rendered from assets/render.html with a browser, then
	downscaled to 1544×500, 772×250 and 1280×640.

.EXAMPLE
	pwsh -File tools/build-images.ps1
#>

[CmdletBinding()]
param(
	[string] $Root = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$masterPath = Join-Path $Root 'assets/icon-master.png'

if (-not (Test-Path $masterPath)) {
	throw "Missing master image: $masterPath"
}

$master = [System.Drawing.Bitmap]::FromFile((Resolve-Path $masterPath))

Write-Host "Master: $($master.Width)×$($master.Height)"

foreach ( $size in 512, 256, 128, 64 ) {
	$bitmap = New-Object System.Drawing.Bitmap($size, $size, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
	$graphics = [System.Drawing.Graphics]::FromImage($bitmap)
	$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
	$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
	$graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
	$graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
	$graphics.Clear([System.Drawing.Color]::Transparent)
	$graphics.DrawImage($master, (New-Object System.Drawing.Rectangle(0, 0, $size, $size)))

	$target = Join-Path $Root "assets/icon-$size.png"
	$bitmap.Save($target, [System.Drawing.Imaging.ImageFormat]::Png)
	$graphics.Dispose()
	$bitmap.Dispose()

	Write-Host "  assets/icon-$size.png"
}

$master.Dispose()
Write-Host 'Done.'
