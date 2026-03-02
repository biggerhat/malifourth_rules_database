<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Errata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds sample errata content: a public errata batch (Rules Errata)
 * and standalone General Errata pages.
 *
 * Usage: php artisan db:seed --class=ErrataSeeder
 */
class ErrataSeeder extends Seeder
{
    private User $admin;

    public function run(): void
    {
        $this->admin = User::where('email', 'admin@test.com')->first()
            ?? User::factory()->create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
            ]);

        $this->command->info('Using admin user: admin@test.com');

        // ─────────────────────────────────────────────────────────
        //  Rules Errata: Public Batch
        // ─────────────────────────────────────────────────────────
        $batch = Batch::create([
            'title' => 'March 2026 Errata',
            'release_notes' => '<p>This errata addresses several balance and clarity issues identified since the initial release of Malifaux 4th Edition.</p>',
            'internal_notes' => null,
            'is_public' => true,
            'created_by' => $this->admin->id,
        ]);

        $batch->approval()->create([
            'initiated_by' => $this->admin->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
            'change_notes' => 'Initial errata batch',
        ]);

        $batch->update([
            'published_at' => now(),
            'published_by' => $this->admin->id,
        ]);

        $this->command->info('Created public errata batch: March 2026 Errata');

        // ─────────────────────────────────────────────────────────
        //  General Errata Pages
        // ─────────────────────────────────────────────────────────
        $entries = $this->errataEntries();
        $count = 0;

        foreach ($entries as $entry) {
            $errata = Errata::create($entry);
            $this->approveAndPublish($errata);
            $count++;
        }

        $this->command->newLine();
        $this->command->info("Done! Seeded {$count} general errata entries and 1 public batch.");
    }

    private function approveAndPublish(Errata $errata): void
    {
        $errata->approval()->create([
            'initiated_by' => $this->admin->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
            'change_notes' => 'Initial errata seed',
        ]);

        $errata->load(['approval', 'previousVersion']);
        $errata->publish($this->admin);
    }

    /**
     * @return array<int, array{title: string, content: string, sort_order: int}>
     */
    private function errataEntries(): array
    {
        return [
            [
                'title' => 'Rulebook Errata – March 2026',
                'content' => '<p><strong>Page 14 – Measuring:</strong> The second paragraph should read: "Players may pre-measure any distances at any time. When measuring, the distance is always measured from the closest point of the acting model\'s base to the closest point of the target."</p>'
                    .'<p><strong>Page 22 – Activation Phase:</strong> Replace the last sentence with: "If a model has already Activated this Turn, it may not Activate again unless an effect specifically allows it to do so."</p>'
                    .'<p><strong>Page 31 – Auras:</strong> Add the following clarification: "Aura effects are checked at the time the triggering condition is met, not at the start or end of the Activation."</p>',
                'sort_order' => 0,
            ],
            [
                'title' => 'Keyword Errata – March 2026',
                'content' => '<p><strong>Arcanists – Sandeep Desai:</strong> The "Beacon of Knowledge" ability should specify "non-Master" in its targeting requirements. Updated text: "Target a friendly non-Master model within 6."</p>'
                    .'<p><strong>Guild – Dashel Barker:</strong> The "Reinforcements" trigger now reads: "After resolving, Summon a Guard model with a Cost of 5 or less within 6 and in base contact with a friendly Guard model."</p>'
                    .'<p><strong>Neverborn – Pandora:</strong> The "Misery" aura has been updated to: "Enemy models within {{aura}}3 that fail a Wp duel suffer 1 damage after resolving."</p>',
                'sort_order' => 1,
            ],
            [
                'title' => 'Tournament Errata – March 2026',
                'content' => '<p><strong>Gaining Grounds – Crew Selection:</strong> Clarification added: "A player may not hire more than one model with the same name into their crew, unless the model has the Versatile keyword or the hiring rules for that keyword specifically allow it."</p>'
                    .'<p><strong>Gaining Grounds – Scheme Timing:</strong> Updated to read: "Scheme markers placed during the End Phase are placed after all other End Phase effects have resolved."</p>',
                'sort_order' => 2,
            ],
        ];
    }
}
