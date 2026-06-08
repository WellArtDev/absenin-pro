<?php

class Upload
{
    public static function selfie(array $file, string $tenantId, string $employeeId): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Response::validationError('Gagal mengupload foto');
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            Response::validationError('Ukuran foto maksimal 5MB');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            Response::validationError('Format foto harus JPEG, PNG, atau WebP');
        }

        $dateFolder = date('Y-m');
        $uploadDir = UPLOAD_DIR . "/{$tenantId}/{$employeeId}/{$dateFolder}";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        $destination = "{$uploadDir}/{$filename}";

        $image = self::createImage($file['tmp_name'], $mimeType);
        $resized = self::resizeImage($image, SELFIE_MAX_WIDTH);
        imagejpeg($resized, $destination, 80);

        imagedestroy($image);
        imagedestroy($resized);

        return "/uploads/{$tenantId}/{$employeeId}/{$dateFolder}/{$filename}";
    }

    private static function createImage(string $path, string $mime): \GdImage
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => throw new \RuntimeException('Unsupported image type'),
        };
    }

    private static function resizeImage(\GdImage $image, int $maxWidth): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= $maxWidth) {
            return $image;
        }

        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = (int) ($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }
}
