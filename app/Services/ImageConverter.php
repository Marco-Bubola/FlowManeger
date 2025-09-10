<?php

namespace App\Services;

class ImageConverter
{
    /**
     * Convert WebP image to JPEG for better compatibility
     *
     * @param string $webpPath
     * @param string $outputPath
     * @param int $quality
     * @return bool
     */
    public static function convertWebpToJpeg($webpPath, $outputPath = null, $quality = 90)
    {
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension is not loaded');
        }

        // Check if WebP support is available
        if (!function_exists('imagecreatefromwebp')) {
            throw new \Exception('WebP support is not available in GD extension');
        }

        // Generate output path if not provided
        if ($outputPath === null) {
            $pathInfo = pathinfo($webpPath);
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.jpg';
        }

        try {
            // Create image resource from WebP
            $webpImage = imagecreatefromwebp($webpPath);

            if ($webpImage === false) {
                throw new \Exception('Failed to create image from WebP file');
            }

            // Convert to JPEG
            $success = imagejpeg($webpImage, $outputPath, $quality);

            // Free up memory
            imagedestroy($webpImage);

            if (!$success) {
                throw new \Exception('Failed to save JPEG file');
            }

            return $outputPath;

        } catch (\Exception $e) {
            throw new \Exception('Error converting WebP to JPEG: ' . $e->getMessage());
        }
    }

    /**
     * Convert WebP image to PNG for transparency support
     *
     * @param string $webpPath
     * @param string $outputPath
     * @return bool
     */
    public static function convertWebpToPng($webpPath, $outputPath = null)
    {
        // Check if GD extension is loaded
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension is not loaded');
        }

        // Check if WebP support is available
        if (!function_exists('imagecreatefromwebp')) {
            throw new \Exception('WebP support is not available in GD extension');
        }

        // Generate output path if not provided
        if ($outputPath === null) {
            $pathInfo = pathinfo($webpPath);
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.png';
        }

        try {
            // Create image resource from WebP
            $webpImage = imagecreatefromwebp($webpPath);

            if ($webpImage === false) {
                throw new \Exception('Failed to create image from WebP file');
            }

            // Enable alpha blending and save alpha channel
            imagealphablending($webpImage, false);
            imagesavealpha($webpImage, true);

            // Convert to PNG
            $success = imagepng($webpImage, $outputPath);

            // Free up memory
            imagedestroy($webpImage);

            if (!$success) {
                throw new \Exception('Failed to save PNG file');
            }

            return $outputPath;

        } catch (\Exception $e) {
            throw new \Exception('Error converting WebP to PNG: ' . $e->getMessage());
        }
    }

    /**
     * Get supported image types for DomPDF
     *
     * @return array
     */
    public static function getSupportedTypes()
    {
        return ['jpg', 'jpeg', 'png', 'gif'];
    }

    /**
     * Check if file type is supported by DomPDF
     *
     * @param string $filePath
     * @return bool
     */
    public static function isSupported($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, self::getSupportedTypes());
    }

    /**
     * Auto-convert unsupported images to supported format
     *
     * @param string $imagePath
     * @return string Path to converted image or original if already supported
     */
    public static function ensureCompatibility($imagePath)
    {
        if (self::isSupported($imagePath)) {
            return $imagePath;
        }

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'webp':
                return self::convertWebpToJpeg($imagePath);
            default:
                throw new \Exception("Unsupported image format: {$extension}");
        }
    }
}
