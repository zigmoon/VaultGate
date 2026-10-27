<#
.SYNOPSIS
	Builds every raster asset of the project from assets/icon-master.png.

.DESCRIPTION
	The master is a 1536×1536 PNG rendered from assets/icon.svg (3× the
	largest icon). Each icon size is an exact integer downscale, so the
	antialiasing stays clean.

	Produces:
	  assets/icon-512.png, icon-256.png, icon-128.png, icon-64.png
	  assets/banner-772x250.png, banner-1544x500.png
	  assets/social-preview-1280x640.png

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
	throw "Missing master image: $masterPath (render assets/icon.svg at 1024 CSS px)."
}

$master = [System.Drawing.Bitmap]::FromFile((Resolve-Path $masterPath))
$accent = [System.Drawing.Color]::FromArgb(255, 79, 70, 229)
$violet = [System.Drawing.Color]::FromArgb(255, 124, 58, 237)

function New-Canvas {
	param([int] $Width, [int] $Height)

	$bitmap = New-Object System.Drawing.Bitmap($Width, $Height, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
	$graphics = [System.Drawing.Graphics]::FromImage($bitmap)
	$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
	$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
	$graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
	$graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
	$graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::ClearTypeGridFit

	return @{ Bitmap = $bitmap; Graphics = $graphics }
}

function Save-Png {
	param($Canvas, [string] $Path)

	$Canvas.Bitmap.Save((Join-Path $Root $Path), [System.Drawing.Imaging.ImageFormat]::Png)
	$Canvas.Graphics.Dispose()
	$Canvas.Bitmap.Dispose()
	Write-Host "  $Path"
}

function New-Icon {
	param([int] $Size, [string] $Path)

	$canvas = New-Canvas -Width $Size -Height $Size
	$canvas.Graphics.Clear([System.Drawing.Color]::Transparent)
	$canvas.Graphics.DrawImage($master, (New-Object System.Drawing.Rectangle(0, 0, $Size, $Size)))
	Save-Png -Canvas $canvas -Path $Path
}

Write-Host 'Icons:'
New-Icon -Size 512 -Path 'assets/icon-512.png'
New-Icon -Size 256 -Path 'assets/icon-256.png'
New-Icon -Size 128 -Path 'assets/icon-128.png'
New-Icon -Size 64  -Path 'assets/icon-64.png'

function New-Banner {
	param([int] $Width, [int] $Height, [string] $Path)

	$canvas = New-Canvas -Width $Width -Height $Height
	$graphics = $canvas.Graphics

	$rect = New-Object System.Drawing.Rectangle(0, 0, $Width, $Height)
	$brush = New-Object System.Drawing.Drawing2D.LinearGradientBrush($rect, $accent, $violet, 20.0)
	$graphics.FillRectangle($brush, $rect)
	$brush.Dispose()

	# Soft light in the upper left corner.
	$glowSize = [int]($Height * 1.9)
	$glowPath = New-Object System.Drawing.Drawing2D.GraphicsPath
	$glowPath.AddEllipse(-[int]($glowSize * 0.35), -[int]($glowSize * 0.55), $glowSize, $glowSize)
	$glow = New-Object System.Drawing.Drawing2D.PathGradientBrush($glowPath)
	$glow.CenterColor = [System.Drawing.Color]::FromArgb(70, 255, 255, 255)
	$glow.SurroundColors = @([System.Drawing.Color]::FromArgb(0, 255, 255, 255))
	$graphics.FillPath($glow, $glowPath)
	$glow.Dispose()
	$glowPath.Dispose()

	$iconSize = [int]($Height * 0.62)
	$iconX = [int]($Height * 0.16)
	$iconY = [int](($Height - $iconSize) / 2)
	$graphics.DrawImage($master, (New-Object System.Drawing.Rectangle($iconX, $iconY, $iconSize, $iconSize)))

	$textX = $iconX + $iconSize + [int]($Height * 0.10)
	$titleSize = [int]($Height * 0.20)
	$subtitleSize = [int]($Height * 0.085)

	$titleFont = New-Object System.Drawing.Font('Segoe UI', $titleSize, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
	$subtitleFont = New-Object System.Drawing.Font('Segoe UI', $subtitleSize, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)

	$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
	$subtitleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(225, 255, 255, 255))

	$title = 'VaultGate'
	$subtitle = 'One password for the whole site'
	$titleMetrics = $graphics.MeasureString($title, $titleFont)
	$subtitleMetrics = $graphics.MeasureString($subtitle, $subtitleFont)
	$totalHeight = $titleMetrics.Height + $subtitleMetrics.Height * 1.15
	$titleY = ($Height - $totalHeight) / 2

	$graphics.DrawString($title, $titleFont, $titleBrush, $textX, $titleY)
	$graphics.DrawString($subtitle, $subtitleFont, $subtitleBrush, $textX, $titleY + $titleMetrics.Height * 1.02)

	$titleFont.Dispose()
	$subtitleFont.Dispose()
	$titleBrush.Dispose()
	$subtitleBrush.Dispose()

	Save-Png -Canvas $canvas -Path $Path
}

Write-Host 'Banners:'
New-Banner -Width 1544 -Height 500 -Path 'assets/banner-1544x500.png'
New-Banner -Width 772  -Height 250 -Path 'assets/banner-772x250.png'

function New-SocialPreview {
	param([int] $Width, [int] $Height, [string] $Path)

	$canvas = New-Canvas -Width $Width -Height $Height
	$graphics = $canvas.Graphics

	$rect = New-Object System.Drawing.Rectangle(0, 0, $Width, $Height)
	$brush = New-Object System.Drawing.Drawing2D.LinearGradientBrush($rect, [System.Drawing.Color]::FromArgb(255, 11, 13, 20), [System.Drawing.Color]::FromArgb(255, 30, 27, 75), 35.0)
	$graphics.FillRectangle($brush, $rect)
	$brush.Dispose()

	$glowSize = 1100
	$glowPath = New-Object System.Drawing.Drawing2D.GraphicsPath
	$glowPath.AddEllipse(($Width - $glowSize / 2), -350, $glowSize, $glowSize)
	$glow = New-Object System.Drawing.Drawing2D.PathGradientBrush($glowPath)
	$glow.CenterColor = [System.Drawing.Color]::FromArgb(90, 99, 102, 241)
	$glow.SurroundColors = @([System.Drawing.Color]::FromArgb(0, 99, 102, 241))
	$graphics.FillPath($glow, $glowPath)
	$glow.Dispose()
	$glowPath.Dispose()

	$iconSize = 300
	$iconX = 96
	$iconY = [int](($Height - $iconSize) / 2)
	$graphics.DrawImage($master, (New-Object System.Drawing.Rectangle($iconX, $iconY, $iconSize, $iconSize)))

	$textX = $iconX + $iconSize + 64
	$titleFont = New-Object System.Drawing.Font('Segoe UI', 92, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
	$subtitleFont = New-Object System.Drawing.Font('Segoe UI', 34, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)
	$metaFont = New-Object System.Drawing.Font('Segoe UI', 25, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)

	$white = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::White)
	$soft = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(215, 226, 232, 240))
	$accentBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255, 165, 180, 252))

	$graphics.DrawString('VaultGate', $titleFont, $white, $textX, 210)
	$graphics.DrawString('Password protection for WordPress', $subtitleFont, $soft, $textX + 4, 315)
	$graphics.DrawString('No third-party service · WordPress 6.5–7.1 · PHP 8.2–8.5', $metaFont, $accentBrush, $textX + 4, 380)

	$titleFont.Dispose()
	$subtitleFont.Dispose()
	$metaFont.Dispose()
	$white.Dispose()
	$soft.Dispose()
	$accentBrush.Dispose()

	Save-Png -Canvas $canvas -Path $Path
}

Write-Host 'Social preview:'
New-SocialPreview -Width 1280 -Height 640 -Path 'assets/social-preview-1280x640.png'

$master.Dispose()
Write-Host 'Done.'
