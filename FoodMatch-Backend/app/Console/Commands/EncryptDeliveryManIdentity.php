<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * One-off migration command.
 *
 * `DeliveryMan::$identity_number` was just switched to Laravel's `encrypted`
 * Eloquent cast. Any row seeded/edited *before* that cast existed still has
 * its `identity_number` stored as plain text in the database. Once the cast
 * is active, Eloquent will try to `Crypt::decryptString()` every value it
 * reads — for the still-plain-text rows that throws a DecryptException and
 * breaks any page that lists delivery men (admin panel, exports, API).
 *
 * This command performs the one-time backfill:
 *   1. Reads every row's raw `identity_number` straight from the database
 *      with the query builder (`DB::table`), bypassing the model's cast so
 *      the old plain-text value can actually be read.
 *   2. For each row, tries to `Crypt::decryptString()` the raw value.
 *        - Success  -> the value is already an encrypted payload (e.g. the
 *                       row was created/saved after the cast went live, or
 *                       this command already ran before). Skip it.
 *        - Failure  -> the value is plain text. Encrypt it and write it
 *                       back.
 *   3. Prints a summary of how many rows were encrypted vs. already
 *      encrypted vs. had nothing to do (null/empty identity_number).
 *
 * Implementation note — why this does NOT do
 * `DeliveryMan::find($id)->update(['identity_number' => $raw])`:
 * that seemingly obvious approach was tried first and throws a
 * DecryptException from inside Eloquent itself. `save()` calls
 * `originalIsEquivalent()` to build the dirty-attribute list, and for an
 * `encrypted`-cast attribute that method decrypts BOTH the new value and
 * the model's *original* (as-fetched) value to compare them. The original
 * value here is still plain text, so that internal decrypt attempt fails
 * before the UPDATE is ever issued — i.e. Eloquent's own dirty-checking
 * makes the "just re-save it through the model" approach impossible for
 * this specific migration (fetch plain text -> save so the cast encrypts
 * it) even though it works fine for ordinary encrypted writes.
 *
 * Instead this command writes the ciphertext directly with a query
 * builder update: `Crypt::encryptString($value)` calls
 * `Encrypter::encrypt($value, false)` internally, which is byte-for-byte
 * the same call Eloquent's `encrypted` cast makes when *it* encrypts a
 * value on save (`castAttributeAsEncryptedString()`). So the stored
 * ciphertext is identical to what the model would have produced, and the
 * `encrypted` cast decrypts it back transparently on every future read.
 *
 * Safe to run more than once (idempotent) — rows that are already
 * encrypted are detected and left untouched.
 */
class EncryptDeliveryManIdentity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:encrypt-delivery-man-identity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off backfill: encrypt any plain-text delivery_men.identity_number left over from before the encrypted cast was added';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $encrypted = 0;
        $alreadyEncrypted = 0;
        $skippedEmpty = 0;
        $total = 0;

        $this->info('Scanning delivery_men.identity_number for plain-text values...');

        // Read RAW rows via the query builder so the model's `encrypted`
        // cast (already active on DeliveryMan) never gets a chance to run
        // and blow up on the old plain-text values.
        DB::table('delivery_men')
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

                    // Plain text: encrypt with the same primitive the
                    // `encrypted` cast itself uses, and write it back with
                    // a plain query builder update (see class docblock for
                    // why going through Eloquent's save() is not viable
                    // here).
                    DB::table('delivery_men')
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

    /**
     * Determine whether a raw DB value is already a Laravel-encrypted
     * payload by attempting to decrypt it. A genuine plain-text identity
     * number (digits/dashes) will never successfully decrypt, so a thrown
     * exception is the signal that it's still plain text.
     */
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
