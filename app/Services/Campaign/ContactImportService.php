<?php

namespace App\Services\Campaign;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactImportService
{
    /**
     * Preview CSV file - retourne les premières lignes pour validation
     */
    public function preview(string $filePath, int $previewCount = 10): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier CSV');
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException('Fichier CSV vide ou invalide');
        }

        // Normaliser les headers
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        // Détecter les colonnes
        $nameCol = $this->findColumn($header, ['name', 'nom', 'prenom', 'full_name']);
        $phoneCol = $this->findColumn($header, ['phone', 'phone_number', 'telephone', 'tel', 'numero', 'whatsapp']);
        $emailCol = $this->findColumn($header, ['email', 'mail', 'e-mail']);

        if ($nameCol === null || $phoneCol === null) {
            fclose($handle);
            throw new \RuntimeException('Le CSV doit contenir au moins les colonnes "name" (ou "nom") et "phone" (ou "telephone")');
        }

        $rows = [];
        $totalRows = 0;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $totalRows++;
            if (count($rows) < $previewCount) {
                $rows[] = [
                    'name'  => trim($row[$nameCol] ?? ''),
                    'phone' => trim($row[$phoneCol] ?? ''),
                    'email' => $emailCol !== null ? trim($row[$emailCol] ?? '') : null,
                ];
            }
        }

        fclose($handle);

        return [
            'headers'    => $header,
            'name_col'   => $nameCol,
            'phone_col'  => $phoneCol,
            'email_col'  => $emailCol,
            'preview'    => $rows,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Import confirmé - importe par lots de 100
     */
    public function import(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier CSV');
        }

        $header = fgetcsv($handle, 0, ',');
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $nameCol = $this->findColumn($header, ['name', 'nom', 'prenom', 'full_name']);
        $phoneCol = $this->findColumn($header, ['phone', 'phone_number', 'telephone', 'tel', 'numero', 'whatsapp']);
        $emailCol = $this->findColumn($header, ['email', 'mail', 'e-mail']);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $contactIds = [];
        $batch = [];
        $userId = Auth::id();

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $name = trim($row[$nameCol] ?? '');
            $phone = trim($row[$phoneCol] ?? '');
            $email = $emailCol !== null ? trim($row[$emailCol] ?? '') : null;

            if (empty($name) || empty($phone)) {
                $skipped++;
                continue;
            }

            $batch[] = [
                'name'       => $name,
                'phone'      => $phone,
                'email'      => $email ?: null,
                'created_by' => $userId,
            ];

            if (count($batch) >= 100) {
                $result = $this->processBatch($batch);
                $imported += $result['imported'];
                $skipped += $result['skipped'];
                $errors = array_merge($errors, $result['errors']);
                $contactIds = array_merge($contactIds, $result['contactIds']);
                $batch = [];
            }
        }

        // Traiter le dernier lot
        if (!empty($batch)) {
            $result = $this->processBatch($batch);
            $imported += $result['imported'];
            $skipped += $result['skipped'];
            $errors = array_merge($errors, $result['errors']);
            $contactIds = array_merge($contactIds, $result['contactIds']);
        }

        fclose($handle);

        return [
            'imported'    => $imported,
            'skipped'     => $skipped,
            'errors'      => array_slice($errors, 0, 20),
            'contact_ids' => $contactIds,
        ];
    }

    private function processBatch(array $batch): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $contactIds = [];

        DB::beginTransaction();
        try {
            foreach ($batch as $row) {
                // Normaliser le téléphone
                $phone = preg_replace('/[\s\-\(\)]/', '', $row['phone']);
                if (!str_starts_with($phone, '+')) {
                    $phone = '+' . $phone;
                }

                // Vérifier le doublon par numéro
                $exists = Contact::where('phone_number', $phone)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                try {
                    $contact = Contact::create([
                        'name'         => $row['name'],
                        'phone_number' => $phone,
                        'email'        => $row['email'],
                        'created_by'   => $row['created_by'],
                    ]);
                    $imported++;
                    $contactIds[] = $contact->id;
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Ligne '{$row['name']}' ({$row['phone']}): {$e->getMessage()}";
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur import CSV batch', ['error' => $e->getMessage()]);
            throw $e;
        }

        return compact('imported', 'skipped', 'errors', 'contactIds');
    }

    private function findColumn(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $index = array_search($candidate, $headers);
            if ($index !== false) {
                return $index;
            }
        }
        return null;
    }
}
