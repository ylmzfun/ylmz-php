<?php

namespace Ylmz\Support;

class FileUpload
{
    private array $file;

    public function __construct(array $file)
    {
        $this->file = $file;
    }

    public function isValid(): bool
    {
        return isset($this->file['error']) && $this->file['error'] === UPLOAD_ERR_OK;
    }

    public function getClientName(): string    { return $this->file['name'] ?? ''; }
    public function getClientType(): string    { return $this->file['type'] ?? ''; }
    public function getSize(): int             { return (int)($this->file['size'] ?? 0); }
    public function getTempPath(): string      { return $this->file['tmp_name'] ?? ''; }
    public function getExtension(): string     { return strtolower(pathinfo($this->getClientName(), PATHINFO_EXTENSION)); }

    /**
     * Move uploaded file to destination.
     */
    public function store(string $directory, ?string $name = null): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('Invalid uploaded file.');
        }

        $dir = rtrim($directory, '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $name ?: uniqid() . '.' . $this->getExtension();
        $path = $dir . '/' . $filename;

        move_uploaded_file($this->getTempPath(), $path);
        return $path;
    }

    /**
     * Validate file type against allowed extensions.
     */
    public function allowedExtensions(array $extensions): bool
    {
        return in_array($this->getExtension(), $extensions, true);
    }

    /**
     * Validate file size against max kilobytes.
     */
    public function maxSize(int $kilobytes): bool
    {
        return $this->getSize() <= $kilobytes * 1024;
    }

    /**
     * Create instance from uploaded file array.
     */
    public static function from(string $key): ?self
    {
        $file = $_FILES[$key] ?? null;
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return new self($file);
    }
}
