<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Support\Facades\Storage;

class ScormService
{
    private string $storagePath;
    
    public function __construct()
    {
        $this->storagePath = storage_path('app/public/scorm');
    }

    public function ensureExtracted(Resource $resource): bool
    {
        $extractDir = $this->storagePath . '/' . $resource->uuid;
        if (is_dir($extractDir)) {
            $files = scandir($extractDir);
            if (count($files) > 2) {
                return true;
            }
        }

        if (!$resource->file_path) {
            logger()->error('SCORM: No file_path for resource', ['uuid' => $resource->uuid]);
            return false;
        }

        $storedPath = storage_path('app/public/' . $resource->file_path);

        // Restore ZIP from DB if file missing from disk
        if (!file_exists($storedPath)) {
            if ($resource->file_data) {
                $dir = dirname($storedPath);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                file_put_contents($storedPath, $resource->file_data);
                logger()->info('SCORM: Restored zip from DB', ['uuid' => $resource->uuid]);
            } else {
                logger()->error('SCORM: Stored file not found and no DB backup', ['uuid' => $resource->uuid]);
                return false;
            }
        }

        logger()->info('SCORM: Re-extracting package', ['uuid' => $resource->uuid, 'path' => $storedPath]);
        $this->extractPackage($resource->uuid, $storedPath);

        return is_dir($extractDir);
    }

    public function isScormPackage(string $filePath): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $hasManifest = $zip->locateName('imsmanifest.xml') !== false;
            if (!$hasManifest) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_ends_with($name, 'imsmanifest.xml')) {
                        $hasManifest = true;
                        break;
                    }
                }
            }
            $zip->close();
            return $hasManifest;
        }
        return false;
    }

    public function extractPackage(string $resourceId, string $filePath): string
    {
        $extractDir = $this->storagePath . '/' . $resourceId;
        if (!is_dir($extractDir)) {
            @mkdir($extractDir, 0755, true);
        }

        if (!is_dir($extractDir) || !is_writable($extractDir)) {
            logger()->error('SCORM: Cannot create or write to extract dir', ['dir' => $extractDir]);
            return $extractDir;
        }

        if (!file_exists($filePath)) {
            logger()->error('SCORM: File not found for extraction', ['path' => $filePath]);
            return $extractDir;
        }

        if (!extension_loaded('zip')) {
            logger()->error('SCORM: zip extension not loaded');
            return $extractDir;
        }

        $zip = new \ZipArchive();
        $status = $zip->open($filePath);
        if ($status === true) {
            $extracted = $zip->extractTo($extractDir);
            $zip->close();
            if (!$extracted) {
                logger()->error('SCORM: extractTo failed', ['dir' => $extractDir]);
            }
        } else {
            logger()->error('SCORM: zip open failed', ['status' => $status, 'path' => $filePath]);
        }

        return $extractDir;
    }

    public function getLaunchFile(string $resourceId): string
    {
        $extractDir = $this->storagePath . '/' . $resourceId;
        
        if (!is_dir($extractDir)) {
            return 'index.html';
        }
        
        $manifestPath = $this->findManifestFile($extractDir);
        
        if ($manifestPath) {
            $launchFile = $this->parseManifestForLaunchFile($manifestPath, $extractDir);
            if ($launchFile) {
                return $launchFile;
            }
        }
        
        return $this->findFirstHtmlFile($extractDir);
    }

    public function getLaunchFilePath(string $resourceId): string
    {
        return $resourceId . '/' . $this->getLaunchFile($resourceId);
    }

    private function findManifestFile(string $dir): ?string
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            if ($file->getFilename() === 'imsmanifest.xml') {
                return $file->getRealPath();
            }
        }
        return null;
    }

    private function parseManifestForLaunchFile(string $manifestPath, string $extractDir): ?string
    {
        $xml = simplexml_load_file($manifestPath);
        if ($xml === false) return null;

        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces[''] ?? '';
        $adlcp = $namespaces['adlcp'] ?? 'http://www.adlnet.org/xsd/adlcp_rootv1p2';
        
        if (isset($xml->resources->resource)) {
            $bestMatch = null;
            foreach ($xml->resources->resource as $resource) {
                $attrs = $resource->attributes();
                
                $scormType = '';
                foreach (['adlcp', '', 'adlseq'] as $prefix) {
                    $nsAttr = $prefix ? ($attrs->attributes($namespaces[$prefix] ?? '') ?? null) : $attrs;
                    if ($nsAttr && isset($nsAttr['scormtype'])) {
                        $scormType = (string)$nsAttr['scormtype'];
                        break;
                    }
                }
                
                $href = (string)$attrs['href'];
                
                if (empty($scormType) || strtolower($scormType) === 'sco') {
                    if (str_contains(strtolower($href), 'html5') || str_contains($href, '.html')) {
                        return $href;
                    }
                    if ($bestMatch === null && !str_contains($href, '.swf')) {
                        $bestMatch = $href;
                    }
                    if ($bestMatch === null) {
                        $bestMatch = $href;
                    }
                }
            }
            if ($bestMatch) return $bestMatch;
        }

        if (isset($xml->organizations->organization)) {
            foreach ($xml->organizations->organization as $org) {
                foreach ($org->item as $item) {
                    $identifierRef = (string)$item['identifierref'];
                    if ($identifierRef && isset($xml->resources->resource)) {
                        foreach ($xml->resources->resource as $resource) {
                            if ((string)$resource['identifier'] === $identifierRef) {
                                $href = (string)$resource->attributes()['href'];
                                if ($href) return $href;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    private function findFirstHtmlFile(string $dir): string
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $file) {
            if (in_array(strtolower($file->getExtension()), ['html', 'htm'])) {
                $relativePath = str_replace($dir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                return str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            }
        }
        return 'index.html';
    }
}
