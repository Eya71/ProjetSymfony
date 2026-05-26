<?php

namespace App\Service;

final class LegacyImagePathResolver
{
    public function __construct(private readonly string $projectDir)
    {
    }

    public function product(?string $path): string
    {
        $raw = trim((string) $path);
        if ($raw === '') {
            return 'files_profil/logo.png';
        }

        $normalized = str_replace('\\', '/', $raw);
        $normalized = preg_replace('#^\.\./+#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('#^/ProjetWeb/#', '', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        $candidates = [];
        if (str_starts_with($normalized, 'files_produit/') || str_starts_with($normalized, 'files_produits/')) {
            $candidates[] = $normalized;
            $candidates[] = str_starts_with($normalized, 'files_produit/')
                ? str_replace('files_produit/', 'files_produits/', $normalized)
                : str_replace('files_produits/', 'files_produit/', $normalized);
        } else {
            $candidates[] = 'files_produit/'.$normalized;
            $candidates[] = 'files_produits/'.$normalized;
        }

        foreach (array_unique($candidates) as $candidate) {
            if ($this->publicFileExists($candidate)) {
                return $candidate;
            }
        }

        $basename = pathinfo($normalized, PATHINFO_BASENAME);
        if ($basename !== '') {
            foreach (['files_produit', 'files_produits', 'files_demandes'] as $dir) {
                $absoluteDir = $this->projectDir.'/public/'.$dir;
                if (!is_dir($absoluteDir)) {
                    continue;
                }

                foreach (glob($absoluteDir.'/*'.$basename.'*') ?: [] as $match) {
                    if (is_file($match)) {
                        return $dir.'/'.basename($match);
                    }
                }
            }
        }

        return 'files_profil/logo.png';
    }

    public function profile(?string $path): string
    {
        $raw = trim((string) $path);
        if ($raw === '') {
            return 'files_profil/logo.png';
        }

        $normalized = str_replace('\\', '/', $raw);
        $normalized = preg_replace('#^\.\./+#', '', $normalized) ?? $normalized;
        $normalized = preg_replace('#^/ProjetWeb/#', '', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        if ($this->publicFileExists($normalized)) {
            return $normalized;
        }

        $candidate = 'files_profil/'.basename($normalized);
        if ($this->publicFileExists($candidate)) {
            return $candidate;
        }

        return 'files_profil/logo.png';
    }

    private function publicFileExists(string $path): bool
    {
        return is_file($this->projectDir.'/public/'.ltrim($path, '/'));
    }
}
