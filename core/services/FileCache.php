<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;
use Exception;

final class FileCache
{

    public string $keyPrefix = '';
    public string $cachePath = APP_RUNTIME . 'cache';
    public string $cacheFileSuffix = '.bin';
    public int $directoryLevel = 1;
    public ?int $fileMode = null;
    public int $dirMode = 0775;

    public function getValue(string $key): false|string
    {
        $cacheFile = $this->getCacheFile($key);

        if (!@file_exists($cacheFile)) {
            return false;
        }

        if (@filemtime($cacheFile) > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $cacheValue;
            }
        }

        return false;
    }

    public function setValue(string $key, string $value, int $duration): bool
    {
        $cacheFile = $this->getCacheFile($key);
        if ($this->directoryLevel > 0) {
            try {
                @self::createDirectory(dirname($cacheFile), $this->dirMode);
            } catch (Exception $e) {
                App::$logger->throwable($e);
                return false;
            }
        }
        // If ownership differs the touch call will fail, so we try to
        // rebuild the file from scratch by deleting it first
        // https://github.com/yiisoft/yii2/pull/16120
        if (is_file($cacheFile) && function_exists('posix_geteuid') && fileowner($cacheFile) !== posix_geteuid()) {
            @unlink($cacheFile);
        }
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($this->fileMode !== null) {
                @chmod($cacheFile, $this->fileMode);
            }
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }

            return @touch($cacheFile, $duration + time());
        }

        $error = error_get_last();
        App::$logger->warning("Unable to write cache file '$cacheFile': {$error['message']}");
        return false;
    }

    protected function getCacheFile(string $normalizedKey): string
    {
        $cacheKey = $normalizedKey;

        if ($this->keyPrefix !== '') {
            // Remove key prefix to avoid generating constant directory levels
            $lenKeyPrefix = strlen($this->keyPrefix);
            $cacheKey = substr_replace($normalizedKey, '', 0, $lenKeyPrefix);
        }

        $cachePath = $this->cachePath;

        if ($this->directoryLevel > 0) {
            for ($i = 0; $i < $this->directoryLevel; ++$i) {
                if (($subDirectory = substr($cacheKey, $i + $i, 2))) {
                    $cachePath .= DIRECTORY_SEPARATOR . $subDirectory;
                }
            }
        }

        return $cachePath . DIRECTORY_SEPARATOR . $normalizedKey . $this->cacheFileSuffix;
    }

    /**
     * @throws Exception
     */
    public static function createDirectory(string $path, int $mode = 0775, bool $recursive = true): bool
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        // recurse if parent dir does not exist, and we are not at the root of the file system.
        if ($recursive && !is_dir($parentDir) && $parentDir !== $path) {
            self::createDirectory($parentDir, $mode);
        }
        try {
            if (!mkdir($path, $mode)) {
                return false;
            }
        } catch (Exception $e) {
            if (!is_dir($path)) {// https://github.com/yiisoft/yii2/issues/9288
                throw new Exception("Failed to create directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        try {
            return chmod($path, $mode);
        } catch (Exception $e) {
            throw new Exception("Failed to change permissions for directory \"$path\": " . $e->getMessage(), $e->getCode(), $e);
        }
    }

}