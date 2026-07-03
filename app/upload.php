<?php
/**
 * File upload handling. Validates type/size, stores under /uploads/<subdir>
 * with a random name, and returns the path relative to /uploads (for the DB).
 */

const UPLOAD_IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const UPLOAD_VIDEO_EXT = ['mp4', 'webm', 'ogg', 'mov'];

const UPLOAD_MIME = [
    'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
    'webp' => 'image/webp', 'gif' => 'image/gif',
    'mp4'  => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg', 'mov' => 'video/quicktime',
];

/**
 * @return array{0:bool,1:?string}  [true, relativePath] | [false, errorMessage]
 */
function save_upload(?array $file, string $subdir, array $allowedExt, int $maxBytes): array
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [false, 'No file uploaded.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'File is larger than the server allows (upload_max_filesize). Use a smaller file or raise the limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File is larger than the form allows.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded — please retry.',
            UPLOAD_ERR_NO_TMP_DIR => 'Server is missing a temp folder for uploads.',
            UPLOAD_ERR_CANT_WRITE => 'Server could not write the uploaded file to disk.',
        ];
        return [false, $messages[$file['error']] ?? ('Upload failed (error code ' . $file['error'] . ').')];
    }
    if ($file['size'] > $maxBytes) {
        return [false, 'File is too large (max ' . round($maxBytes / 1048576) . ' MB).'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, 'Invalid upload.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return [false, 'Unsupported file type (.' . e($ext) . ').'];
    }

    // Verify the real MIME matches the claimed extension.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']) ?: '';
    $expected = UPLOAD_MIME[$ext] ?? null;
    if ($expected && $mime !== $expected && !($ext === 'jpg' && $mime === 'image/jpeg')) {
        return [false, 'File contents do not match its extension.'];
    }

    $dir = rtrim(UPLOAD_DIR, '/') . '/' . trim($subdir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return [false, 'Cannot create upload directory.'];
    }

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [false, 'Could not store the uploaded file.'];
    }

    return [true, trim($subdir, '/') . '/' . $name];
}

function save_image(?array $file, string $subdir, int $maxMB = 8): array
{
    return save_upload($file, $subdir, UPLOAD_IMAGE_EXT, $maxMB * 1048576);
}

function save_video(?array $file, string $subdir, int $maxMB = 60): array
{
    return save_upload($file, $subdir, UPLOAD_VIDEO_EXT, $maxMB * 1048576);
}

/** Delete a previously stored upload (path relative to /uploads). */
function delete_upload(?string $rel): void
{
    if (!$rel) {
        return;
    }
    $path = realpath(rtrim(UPLOAD_DIR, '/') . '/' . ltrim($rel, '/'));
    // Guard against path traversal: must resolve inside UPLOAD_DIR.
    $base = realpath(UPLOAD_DIR);
    if ($path && $base && str_starts_with($path, $base) && is_file($path)) {
        @unlink($path);
    }
}
