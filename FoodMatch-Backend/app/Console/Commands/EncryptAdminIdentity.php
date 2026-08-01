<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * One-off backfill: encrypt any plain-text admins.identity_number left over
 * from before Admin::$casts got the `encrypted` cast. Same approach as
 * EncryptDeliveryManIdentity — see that class's docblock for why this reads
 * raw rows via the query builder and writes ciphertext back directly instead
 * of going through Eloquent's save().
 */
class EncryptAdminIdentity extends Command
{
    protected $signature = 'app:encrypt-admin-identity';

    protected $description = 'One-off backfill: encrypt any plain-text admins.identity_number left over from before the encrypted cast was added';

    public function handle(): int
    {
        $encrypted = 0;
        $alreadyEncrypted = 0;
        $skippedEmpty = 0;
        $total = 0;

        $this->info('Scanning admins.identity_number for plain-text values...');

        DB::table('admins')
            ->orderBy('id')
            ->select('id', 'identity_number')
            ->chunkById(200, function ($rows) use (&$encrypted, &$alreadyEncrypted, &$skippedEmpty, &$total) {
                foreach ($rows as $row) {
                    $total++;
                    $raw = $row->identity_number;

                    if ($raw === null || $raw === '') {
                        $skippedEmpty++;
                        continue;
                    }

                    if ($this->isAlreadyEncrypted($raw)) {
                        $alreadyEncrypted++;
                        continue;
                    }

                    DB::table('admins')
                        ->where('id', $row->id)
                        ->update(['identity_number' => Crypt::encryptString($raw)]);
                    $encrypted++;
                }
            });

        $this->newLine();
        $this->info('Done.');
        $this->line("Total rows scanned:        {$total}");
        $this->line("Newly encrypted:           {$encrypted}");
        $this->line("Already encrypted (skipped): {$alreadyEncrypted}");
        $this->line("Empty/null (skipped):       {$skippedEmpty}");

        return self::SUCCESS;
    }

    private function isAlreadyEncrypted(string $raw): bool
    {
        try {
            Crypt::decryptString($raw);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
