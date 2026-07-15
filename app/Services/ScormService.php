<?php

namespace App\Services;

class ScormService
{
    private string $storagePath;
    
    public function __construct()
    {
        $this->storagePath = storage_path('app/public/scorm');
    }

    /**
     * Check if a zip file is a SCORM package by looking for imsmanifest.xml
     */
    public function isScormPackage(string $filePath): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $hasManifest = $zip->locateName('imsmanifest.xml') !== false;
            if (!$hasManifest) {
                // Also check in subdirectories
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

    /**
     * Extract SCORM package to storage
     */
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

    /**
     * Get the launch file path from imsmanifest.xml
     * Prefers HTML5 over Flash
     */
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
        
        // Fallback: find any HTML file
        return $this->findFirstHtmlFile($extractDir);
    }

    /**
     * Get full launch file path (resourceId/launchFile)
     */
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

        // Register SCORM namespaces
        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces[''] ?? '';
        $adlcp = $namespaces['adlcp'] ?? 'http://www.adlnet.org/xsd/adlcp_rootv1p2';
        
        // Try to find the launch file from <resource> with scormType="sco"
        if (isset($xml->resources->resource)) {
            $bestMatch = null;
            foreach ($xml->resources->resource as $resource) {
                $attrs = $resource->attributes();
                
                // Get scormType from various possible namespaces
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
                    // Prefer HTML5
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

        // Try organizations > item approach (SCORM 2004)
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
