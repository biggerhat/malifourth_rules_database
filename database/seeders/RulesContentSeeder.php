<?php

namespace Database\Seeders;

use App\Enums\IndexTypeEnum;
use App\Models\Index;
use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds realistic Malifaux rules content with version history,
 * approvals, publishing, cross-references, nested tags, and
 * symbols in titles for comprehensive manual testing.
 *
 * Usage: php artisan db:seed --class=RulesContentSeeder
 */
class RulesContentSeeder extends Seeder
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

        $pages = $this->seedPages();
        $sections = $this->seedSections();
        $indices = $this->seedIndices();
        $this->addCrossReferences($pages, $sections, $indices);

        $this->command->newLine();
        $this->command->info('Done! Seeded '.count($pages).' pages, '.count($sections).' sections, '.count($indices).' indices.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────

    private function approveAndPublish(Page|Section|Index $model, string $changeNotes = ''): void
    {
        $model->approval()->create([
            'initiated_by' => $this->admin->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
            'change_notes' => $changeNotes,
        ]);

        $model->load(['approval', 'previousVersion']);
        $model->publish($this->admin);
    }

    /**
     * Create a version chain for a model.
     *
     * @param  class-string<Page|Section|Index>  $modelClass
     * @param  array<int, array>  $versions  Each element = attributes for that version
     * @param  array<int, string>  $changeNotes  Change notes per version
     * @return Page|Section|Index The current (latest) version
     */
    private function createVersionChain(string $modelClass, array $versions, array $changeNotes = []): Page|Section|Index
    {
        $previous = null;
        $original = null;

        foreach ($versions as $i => $attrs) {
            if ($previous) {
                $attrs['previous'] = $previous->id;
                $attrs['original'] = $original->id;
            }

            $model = $modelClass::create($attrs);
            $this->approveAndPublish($model, $changeNotes[$i] ?? 'Version '.($i + 1));

            if (! $original) {
                $original = $model;
            }
            $previous = $model;
        }

        return $previous;
    }

    // ═════════════════════════════════════════════════════════════
    //  PAGES
    // ═════════════════════════════════════════════════════════════

    private function seedPages(): array
    {
        $pages = [];

        // ── Page 1: "The Rules of Malifaux" — 2 versions ──────────────

        $this->command->info('  Page: The Rules of Malifaux (2 versions)');

        $pages['rules'] = $this->createVersionChain(Page::class, [
            ['title' => 'The Rules of Malifaux', 'page_number' => 1, 'book_page_numbers' => 'pp. 1-2', 'content' => <<<'C'
{{b}}Welcome to Malifaux!{{/b}}

Malifaux is a character-driven skirmish game set in an alternate world where magic and technology collide. Players build Crews led by powerful Masters and their Henchmen, then battle for control of the mysterious Soulstones that power this strange realm.

{{b}}What You Need to Play{{/b}}

To play a game of Malifaux, each player will need the following:
- A Crew of miniatures (typically 5-8 models)
- A Fate Deck (a standard deck of playing cards)
- A measuring tape marked in inches
- A 3' x 3' play area with terrain

{{b}}Core Concepts{{/b}}

Malifaux uses a unique card-based mechanic instead of dice. Each player has a Fate Deck and a hand of cards (the Control Hand) that can be used to cheat fate and influence the outcome of duels.

{{b}}Soulstones{{/b}} {{soulstone /}}

Soulstones are a precious resource in Malifaux. Masters and some Henchmen can use Soulstones to add suits to duels, draw additional cards, or reduce damage. Each Soulstone {{soulstone /}} can only be used once per game.
C],
            ['title' => 'The Rules of Malifaux', 'page_number' => 1, 'book_page_numbers' => 'pp. 1-2', 'content' => <<<'C'
{{xl}}{{b}}Welcome to Malifaux Third Edition!{{/b}}{{/xl}}

Malifaux is a character-driven skirmish game set in an alternate world where magic and technology collide. Players build Crews led by powerful Masters and their Henchmen, then battle for control of the mysterious {{soulstone /}} Soulstones that power this strange realm.

{{b}}What You Need to Play{{/b}}

To play a game of Malifaux, each player will need the following:
- A Crew of miniatures (typically 5-8 models)
- A Fate Deck (a standard deck of playing cards with two Jokers)
- A measuring tape marked in inches
- A 3' x 3' play area with terrain
- Tokens or markers for Conditions and effects

{{b}}Core Concepts{{/b}}

Malifaux uses a unique card-based mechanic instead of dice. Each player has a Fate Deck and a hand of cards (the {{b}}Control Hand{{/b}}) that can be used to {{i}}cheat fate{{/i}} and influence the outcome of duels. The Control Hand is refreshed at the start of each Turn.

{{b}}{{soulstone /}} Soulstones{{/b}}

Soulstones are a precious resource in Malifaux. Masters and some Henchmen can use {{soulstone /}} Soulstones to:
- Add a suit ({{crow /}} {{ram /}} {{tome /}} {{mask /}}) to a duel total
- Draw additional cards during a duel
- Reduce incoming damage
- Prevent certain conditions

Each Soulstone can only be used once per game, so spend them wisely!

{{b}}Summoning{{/b}}

When a model is Summoned, it is placed within the specified range and gains the {{b}}Slow{{/b}} condition. Summoned models come into play with full Health but cannot activate during the Turn they were Summoned unless an Ability specifically states otherwise.
C],
        ], [
            'Initial rules page',
            'Clarified summoning rules, added suit icons, formatting improvements',
        ]);

        // ── Page 2: "Encounter Setup" — 3 versions ────────────────────

        $this->command->info('  Page: Encounter Setup (3 versions)');

        $pages['encounter'] = $this->createVersionChain(Page::class, [
            ['title' => 'Encounter Setup', 'page_number' => 2, 'book_page_numbers' => 'pp. 3-4', 'content' => <<<'C'
{{b}}Setting Up an Encounter{{/b}}

Before a game begins, players must set up the encounter following these steps in order.

{{b}}Step 1: Determine Encounter Size{{/b}}

Players agree on an Encounter Size, which determines the number of {{soulstone /}} Soulstones each player has to hire their Crew. The standard game size is {{b}}50 Soulstones{{/b}}.

{{b}}Step 2: Choose Faction{{/b}}

Each player declares their Faction.

{{b}}Step 3: Determine Strategy{{/b}}

Flip a card from the Fate Deck to determine the Strategy. The Strategy is the primary objective both players are competing to achieve.

{{b}}Step 4: Choose Schemes{{/b}}

A pool of five Schemes is generated. Each player secretly selects two Schemes they will attempt to score.

{{b}}Step 5: Hire Crews{{/b}}

Each player hires their Crew, spending Soulstones to add models. Each Crew must include exactly one Master.
C],
            ['title' => 'Encounter Setup', 'page_number' => 2, 'book_page_numbers' => 'pp. 3-4', 'content' => <<<'C'
{{b}}Setting Up an Encounter{{/b}}

Before a game begins, players must set up the encounter following these steps in order.

{{b}}Step 1: Determine Encounter Size{{/b}}

Players agree on an Encounter Size, which determines the number of {{soulstone /}} Soulstones each player has to hire their Crew. The standard game size is {{b}}50 Soulstones{{/b}}.

{{b}}Step 2: Choose Faction{{/b}}

Each player declares their Faction. Malifaux features several Factions, each with a unique playstyle.

{{b}}Step 3: Determine Strategy{{/b}}

Flip a card from the Fate Deck to determine the Strategy.

{{b}}Step 4: Choose Schemes{{/b}}

A pool of five Schemes is generated. Each player secretly selects two Schemes.

{{b}}Step 5: Hire Crews (Updated){{/b}}

Each player hires their Crew, spending {{soulstone /}} to add models:
- Each Crew must include exactly one {{b}}Master{{/b}}
- A Crew may include one {{b}}Henchman{{/b}} as second-in-command
- {{b}}Versatile{{/b}} models may be hired into any Keyword Crew without penalty
- Out-of-Keyword models cost {{b}}+1 {{soulstone /}}{{/b}} to hire
- Unspent Soulstones (max 7) are added to the Crew's Soulstone Pool
C],
            ['title' => 'Encounter Setup', 'page_number' => 2, 'book_page_numbers' => 'pp. 3-5', 'content' => <<<'C'
{{b}}Setting Up an Encounter{{/b}}

Before a game begins, players must set up the encounter following these steps in order.

{{b}}Step 1: Determine Encounter Size{{/b}}

Players agree on an Encounter Size, which determines the number of {{soulstone /}} Soulstones each player has to hire their Crew. The standard game size is {{b}}50 Soulstones{{/b}}.

{{lg}}Common Encounter Sizes:{{/lg}}
- {{b}}Henchman Hardcore:{{/b}} 22 {{soulstone /}} — fast games, no Master
- {{b}}Standard:{{/b}} 50 {{soulstone /}} — the default tournament format
- {{b}}Epic:{{/b}} 65 {{soulstone /}} — large games with more options

{{b}}Step 2: Choose Faction{{/b}}

Each player declares their Faction. Malifaux features several Factions, each with a unique playstyle.

{{b}}Step 3: Determine Strategy{{/b}}

Flip a card from the Fate Deck to determine the Strategy.

{{b}}Step 4: Choose Schemes{{/b}}

A pool of five Schemes is generated. Each player secretly selects two Schemes.

{{b}}Step 5: Hire Crews{{/b}}

Each player hires their Crew, spending {{soulstone /}} to add models:
- Each Crew must include exactly one {{b}}Master{{/b}}
- A Crew may include one {{b}}Henchman{{/b}} as second-in-command
- {{b}}Versatile{{/b}} models may be hired without penalty
- Out-of-Keyword models cost {{b}}+1 {{soulstone /}}{{/b}} to hire
- Unspent Soulstones (max 7) are added to the Crew's Soulstone Pool

{{b}}Step 6: Deploy Crews{{/b}}

Players deploy their Crews into Deployment Zones determined by the Strategy card:
- {{b}}Standard:{{/b}} Within 6" of your table edge
- {{b}}Corner:{{/b}} Within 8" of your chosen corner
- {{b}}Wedge:{{/b}} Diagonal zones extending 12" from opposite corners
- {{b}}Flank:{{/b}} Within 6" of a long table edge

Models must be fully within the Deployment Zone when placed. The player who won the Initiative flip deploys first.
C],
        ], [
            'Initial encounter setup',
            'Updated hiring rules with Soulstone costs',
            'Added encounter sizes, deployment zones, formatting',
        ]);

        // ── Page 3: "The Activation Phase" — 1 version ────────────────

        $this->command->info('  Page: The Activation Phase (1 version)');

        $pages['activation'] = $this->createVersionChain(Page::class, [
            ['title' => 'The Activation Phase', 'page_number' => 3, 'book_page_numbers' => 'pp. 6-7', 'content' => <<<'C'
{{b}}The Activation Phase{{/b}}

During the Activation Phase, players alternate activating models. The player with {{b}}fewer unactivated models{{/b}} activates first (if tied, flip for Initiative).

{{b}}Activating a Model{{/b}}

When a model Activates, it generates {{b}}2 Action Points (AP){{/b}} that it may spend on Actions.

{{b}}Types of Actions{{/b}}

- {{melee /}} {{b}}Melee Actions{{/b}}: Close-combat attacks within engagement range
- {{missile /}} {{b}}Ranged Actions{{/b}}: Attacks within the Action's listed range
- {{b}}Tactical Actions{{/b}}: Non-attack Actions providing various effects

{{b}}Action Points{{/b}}

Each Action costs 1 AP unless otherwise noted. A model may take the same Action multiple times unless it has the {{i}}Once Per Activation{{/i}} restriction.

{{b}}Pass{{/b}}

If a player has more unactivated models than their opponent, they may {{b}}Pass{{/b}} instead of activating. A player {{u}}cannot{{/u}} Pass if both players have equal unactivated models.

{{b}}End of the Activation Phase{{/b}}

Once all models have Activated, play moves to the End Phase.
C],
        ], ['Initial activation phase rules']);

        // ── Page 4: "General Actions" — 1 version ─────────────────────

        $this->command->info('  Page: General Actions (1 version)');

        $pages['general_actions'] = $this->createVersionChain(Page::class, [
            ['title' => 'General Actions', 'page_number' => 4, 'book_page_numbers' => 'pp. 8-9', 'content' => <<<'C'
{{b}}General Actions{{/b}}

The following Actions are available to all models unless restricted.

{{hr /}}

{{lg}}{{b}}Walk{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 1 AP{{/i}}{{/sm}}

This model moves up to its {{b}}Mv{{/b}} in inches. Movement must follow all movement rules, including engagement range restrictions.

{{hr /}}

{{lg}}{{b}}Charge{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 2 AP{{/i}}{{/sm}}

This model moves up to its {{b}}Mv + 2"{{/b}} toward a target model, then takes a {{melee /}} Melee Action against that target.

{{hr /}}

{{lg}}{{b}}Interact{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 1 AP{{/i}}{{/sm}}

This model interacts with a Scheme Marker, Strategy Marker, or other game element within 1". A model {{u}}cannot{{/u}} Interact while Engaged.

{{hr /}}

{{lg}}{{b}}Concentrate{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 1 AP, Once Per Activation{{/i}}{{/sm}}

This model gains {{b}}Focused {{positive /}}{{/b}}. The Focused Condition allows the model to add a {{positive /}} to its next duel.

{{hr /}}

{{lg}}{{b}}Disengage{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 2 AP{{/i}}{{/sm}}

This model may move up to its Mv in inches, even if within an enemy model's Engagement Range.

{{hr /}}

{{lg}}{{b}}Drop It!{{/b}}{{/lg}}
{{sm}}{{i}}Tactical — 1 AP{{/i}}{{/sm}}

Remove a friendly Scheme Marker within 1" of this model.
C],
        ], ['Initial general actions page']);

        // ── Page 5: "Melee & Ranged Combat" — 2 versions ──────────────

        $this->command->info('  Page: Melee & Ranged Combat (2 versions)');

        $pages['combat'] = $this->createVersionChain(Page::class, [
            ['title' => '{{melee /}} Melee & {{missile /}} Ranged Combat', 'page_number' => 5, 'book_page_numbers' => 'pp. 10-12', 'content' => <<<'C'
{{xl}}{{b}}{{melee /}} Melee & {{missile /}} Ranged Combat{{/b}}{{/xl}}

Combat in Malifaux is resolved through Attack Actions. Each Attack Action targets one or more enemy models and requires an Opposed Duel.

{{b}}{{melee /}} Melee Actions{{/b}}

Melee Actions target models within the attacker's {{b}}Engagement Range{{/b}} (typically {{b}}1"{{/b}} unless stated otherwise). A model is considered {{b}}Engaged{{/b}} if it is within an enemy model's Engagement Range.

{{i}}Engagement Rules:{{/i}}
- A model {{u}}cannot{{/u}} take {{missile /}} Ranged Actions while Engaged
- A model {{u}}cannot{{/u}} take the {{b}}Interact{{/b}} Action while Engaged
- Moving out of engagement range provokes {{b}}Disengaging Strikes{{/b}}

{{b}}{{missile /}} Ranged Actions{{/b}}

Ranged Actions target models within the Action's listed range. They require {{b}}Line of Sight (LoS){{/b}} to the target.

{{b}}Cover{{/b}}

If a Ranged Action's LoS passes within 1" of blocking terrain or another model, the target is in {{b}}Cover{{/b}} and gains a {{positive /}} to its defense flip.

{{b}}Resolving an Attack{{/b}}

1. Declare the Action and choose a target
2. Both players flip — attacker uses the Action's stat, defender uses the relevant defense
3. Both players may Cheat Fate
4. Compare totals — if the attacker wins, the attack succeeds
5. Determine damage
C],
            ['title' => '{{melee /}} Melee & {{missile /}} Ranged Combat', 'page_number' => 5, 'book_page_numbers' => 'pp. 10-13', 'content' => <<<'C'
{{xl}}{{b}}{{melee /}} Melee & {{missile /}} Ranged Combat{{/b}}{{/xl}}

Combat in Malifaux is resolved through Attack Actions. Each Attack Action targets one or more enemy models and requires an Opposed Duel.

{{b}}{{melee /}} Melee Actions{{/b}}

Melee Actions target models within the attacker's {{b}}Engagement Range{{/b}} (typically {{b}}1"{{/b}} unless stated otherwise). A model is considered {{b}}Engaged{{/b}} if it is within an enemy model's Engagement Range.

{{i}}Engagement Rules:{{/i}}
- A model {{u}}cannot{{/u}} take {{missile /}} Ranged Actions while Engaged (unless it has the {{b}}Gun Fighter{{/b}} ability)
- A model {{u}}cannot{{/u}} take the {{b}}Interact{{/b}} Action while Engaged
- Moving out of engagement range provokes {{b}}Disengaging Strikes{{/b}} — the enemy model takes a free {{melee /}} Action against the moving model

{{b}}{{missile /}} Ranged Actions{{/b}}

Ranged Actions target models within the Action's listed range. They require {{b}}Line of Sight (LoS){{/b}} to the target.

{{b}}Cover{{/b}}

If a Ranged Action's LoS passes within 1" of blocking terrain or another model, the target is in {{b}}Cover{{/b}} and gains a {{positive /}} to its defense flip.

{{b}}Friendly Fire{{/b}}

When a {{missile /}} Ranged Action targets a model that is Engaged with friendly models, the attacking model suffers a {{negative /}} to the attack flip. {{sm}}(This does not apply to Actions with the {{b}}Blast{{/b}} keyword.){{/sm}}

{{b}}Resolving an Attack{{/b}}

1. Declare the Action and choose a target
2. Both players flip — attacker uses the Action's stat, defender uses the relevant defense
3. Both players may Cheat Fate (non-active player cheats first)
4. Compare totals — if the attacker wins, the attack succeeds
5. Determine damage — flip a card and consult the damage profile
6. Declare Triggers ({{crow /}} {{ram /}} {{tome /}} {{mask /}}) if applicable

{{b}}Damage Profiles{{/b}}

Each Attack Action has a damage profile written as {{b}}X/Y/Z{{/b}}:
- {{b}}X{{/b}} = Weak damage (attacker lost or tied by 0-4)
- {{b}}Y{{/b}} = Moderate damage (attacker won by 5-9)
- {{b}}Z{{/b}} = Severe damage (attacker won by 10+)

{{b}}{{ram /}} Critical Strike{{/b}} and similar Triggers can modify the final damage.
C],
        ], [
            'Initial combat rules',
            'Added friendly fire, disengaging strikes, damage profiles',
        ]);

        // ── Page 6: "Soulstone Use" — 3 versions ──────────────────────

        $this->command->info('  Page: Soulstone Use (3 versions)');

        $pages['soulstones'] = $this->createVersionChain(Page::class, [
            ['title' => '{{soulstone /}} Soulstone Use', 'page_number' => 6, 'book_page_numbers' => 'p. 14', 'content' => <<<'C'
{{xl}}{{b}}{{soulstone /}} Soulstone Use{{/b}}{{/xl}}

Only {{b}}Masters{{/b}} and {{b}}Henchmen{{/b}} may use Soulstones from the Crew's Soulstone Pool. Each Soulstone may only be used once.

{{b}}Add a Suit{{/b}}

After flipping for a duel, a model may spend a {{soulstone /}} to add a suit of its choice to the duel total. This is useful for meeting Trigger requirements.

{{b}}Reduce Damage{{/b}}

When a model would suffer damage, it may spend a {{soulstone /}} to reduce the damage by the value of a flipped card (minimum 1 damage).
C],
            ['title' => '{{soulstone /}} Soulstone Use', 'page_number' => 6, 'book_page_numbers' => 'p. 14', 'content' => <<<'C'
{{xl}}{{b}}{{soulstone /}} Soulstone Use{{/b}}{{/xl}}

Only {{b}}Masters{{/b}} and {{b}}Henchmen{{/b}} may use Soulstones from the Crew's Soulstone Pool. Each Soulstone may only be used once.

{{b}}Add a Suit{{/b}} {{soulstone /}}

After flipping for a duel, spend a {{soulstone /}} to add a suit ({{crow /}} {{ram /}} {{tome /}} or {{mask /}}) of your choice to the duel total. This is useful for meeting Trigger requirements.

{{b}}Reduce Damage{{/b}} {{soulstone /}}

When a model would suffer damage, spend a {{soulstone /}} and flip a card — reduce the damage suffered by the value of the flipped card {{sm}}(minimum 1 damage after reduction){{/sm}}.

{{b}}Draw Cards{{/b}} {{soulstone /}}

Before flipping for a duel, a model may spend a {{soulstone /}} to draw 2 additional cards and then discard 2 cards from hand.
C],
            ['title' => '{{soulstone /}} Soulstone Use', 'page_number' => 6, 'book_page_numbers' => 'pp. 14-15', 'content' => <<<'C'
{{xl}}{{b}}{{soulstone /}} Soulstone Use{{/b}}{{/xl}}

Only {{b}}Masters{{/b}} and {{b}}Henchmen{{/b}} may use {{soulstone /}} Soulstones from the Crew's Soulstone Pool. Each Soulstone may only be used once per game.

{{lg}}Soulstone Abilities:{{/lg}}

{{hr /}}

{{b}}Add a Suit{{/b}} {{soulstone /}}

After flipping for a duel, spend a {{soulstone /}} to add a suit of your choice to the duel total:
- {{crow /}} {{b}}Crow{{/b}} — Death, decay, and necromancy
- {{ram /}} {{b}}Ram{{/b}} — Strength, resilience, and brute force
- {{tome /}} {{b}}Tome{{/b}} — Magic, knowledge, and arcane power
- {{mask /}} {{b}}Mask{{/b}} — Speed, trickery, and deception

{{hr /}}

{{b}}Reduce Damage{{/b}} {{soulstone /}}

When suffering damage, spend a {{soulstone /}} and flip a card — reduce the damage by the value of the flipped card {{sm}}(minimum 1 damage after reduction){{/sm}}.

If the {{b}}Red Joker{{/b}} is flipped, the damage is reduced to 0.

{{hr /}}

{{b}}Draw Cards{{/b}} {{soulstone /}}

Before flipping for a duel, spend a {{soulstone /}} to draw 2 additional cards and then discard 2 cards from hand. This improves your Control Hand quality.

{{hr /}}

{{b}}Prevent Conditions{{/b}} {{soulstone /}}

When a model would gain a Condition from an enemy Action, it may spend a {{soulstone /}} to prevent that Condition from being applied. {{sm}}(This does not prevent Conditions gained from friendly effects or Abilities.){{/sm}}

{{hr /}}

{{b}}{{i}}Strategic Note:{{/i}}{{/b}} Soulstones are a finite resource. Spending them early provides immediate power, but saving them ensures flexibility in later Turns. Most competitive players recommend keeping at least 2 {{soulstone /}} in reserve.
C],
        ], [
            'Initial Soulstone rules',
            'Added Draw Cards ability, suit descriptions',
            'Added Prevent Conditions, strategic note, full formatting overhaul',
        ]);

        // ── Page 7: "Terrain & Movement" — 1 version ──────────────────

        $this->command->info('  Page: Terrain & Movement (1 version)');

        $pages['terrain'] = $this->createVersionChain(Page::class, [
            ['title' => 'Terrain & Movement', 'page_number' => 7, 'book_page_numbers' => 'pp. 16-18', 'content' => <<<'C'
{{b}}Terrain & Movement{{/b}}

Terrain plays a critical role in Malifaux, affecting movement, Line of Sight, and tactical positioning.

{{b}}Terrain Types{{/b}}

{{b}}Open Terrain{{/b}}
No movement penalty. Models move freely through open ground.

{{b}}Severe Terrain{{/b}}
Models must spend {{b}}2" of movement for every 1"{{/b}} moved through Severe Terrain. Models with the {{b}}Unimpeded{{/b}} ability ignore this penalty.

{{b}}Hazardous Terrain{{/b}}
When a model moves through or ends its movement in Hazardous Terrain, it {{b}}suffers 1 damage{{/b}}. Some Hazardous Terrain may inflict additional effects or conditions (such as {{b}}Burning +1{{/b}}).

{{b}}Impassable Terrain{{/b}}
Models {{u}}cannot{{/u}} move through Impassable Terrain under any circumstances. Models with {{b}}Flight{{/b}} or {{b}}Incorporeal{{/b}} may move over but not through Impassable Terrain.

{{b}}Climbable Terrain{{/b}}
Models may climb vertical surfaces at a cost of {{b}}2" of movement per 1"{{/b}} climbed. When a model reaches the top, it may stop on the terrain feature if there is sufficient space.

{{hr /}}

{{b}}Line of Sight{{/b}}

Line of Sight (LoS) is determined by drawing an imaginary line from any part of the acting model's base to any part of the target model's base. If the line passes through blocking terrain, LoS is blocked.

{{b}}Height{{/b}}

Terrain features have a Height value (Ht). A model on elevated terrain may draw LoS over lower terrain features. Models {{b}}at least 1" higher{{/b}} than an intervening terrain piece can see over it.

{{b}}Shadow{{/b}}

Terrain features cast a {{b}}Shadow{{/b}} equal to their Height on the opposite side from the acting model. Models partially within the Shadow gain {{b}}Concealment{{/b}} {{sm}}({{positive /}} to defense flips against {{missile /}} Actions){{/sm}}.
C],
        ], ['Initial terrain and movement rules']);

        // ── Page 8: "The End Phase" — 2 versions ──────────────────────

        $this->command->info('  Page: The End Phase (2 versions)');

        $pages['end_phase'] = $this->createVersionChain(Page::class, [
            ['title' => 'The End Phase', 'page_number' => 8, 'book_page_numbers' => 'p. 19', 'content' => <<<'C'
{{b}}The End Phase{{/b}}

After all models have Activated, the End Phase begins. The following steps are resolved in order:

{{b}}Step 1: Resolve "End of Turn" Effects{{/b}}

Resolve any effects that trigger "at the end of the Turn." This includes:
- {{b}}Burning{{/b}}: Suffer X damage, then reduce Burning by 1
- {{b}}Poison{{/b}}: Suffer 1 damage per 3 Poison, then reduce by 1
- Any Ability that specifies "at the end of the Turn"

{{b}}Step 2: Score VP{{/b}}

Both players check their Strategy and Schemes and score any Victory Points earned this Turn.

{{b}}Step 3: Draw Cards{{/b}}

Each player draws cards until their Control Hand reaches its maximum size (usually 6).

{{b}}Step 4: Check for Game End{{/b}}

If this was the final Turn (usually Turn 5), the game ends and final scores are tallied.
C],
            ['title' => 'The End Phase', 'page_number' => 8, 'book_page_numbers' => 'pp. 19-20', 'content' => <<<'C'
{{b}}The End Phase{{/b}}

After all models have Activated, the End Phase begins. The following steps are resolved in order:

{{b}}Step 1: Resolve "End of Turn" Effects{{/b}}

Resolve any effects that trigger "at the end of the Turn." This includes:
- {{b}}Burning +X{{/b}}: Suffer X damage, then reduce Burning by 1
- {{b}}Poison +X{{/b}}: Suffer 1 damage per full 3 Poison, then reduce by 1
- {{b}}Blighted +X{{/b}}: Does not resolve directly — interacts with certain Abilities
- Any model Ability that specifies "at the end of the Turn"

{{sm}}{{i}}Timing Note: If multiple effects trigger simultaneously, the Active player resolves theirs first.{{/i}}{{/sm}}

{{b}}Step 2: Score VP{{/b}}

Both players check their Strategy and Schemes and score any Victory Points (VP) earned this Turn:
- {{b}}Strategy{{/b}}: Typically worth 1 VP per Turn (max 4 VP total)
- {{b}}Schemes{{/b}}: Each Scheme is worth up to 2 VP

{{b}}Step 3: Draw Cards{{/b}}

Each player draws cards until their Control Hand reaches its maximum size (usually {{b}}6 cards{{/b}}).

{{b}}Step 4: Check for Game End{{/b}}

If this was Turn 5 (or another agreed-upon final Turn), the game ends:
- Tally final VP scores
- The player with the {{b}}most VP wins{{/b}}
- If tied on VP, the game is a {{b}}draw{{/b}}

{{hr /}}

{{b}}Between Turns{{/b}}

Between the End Phase and the next Turn's Start Phase, clear any "until the end of the Turn" effects. Remove models that were killed this Turn from the board if they haven't already been removed.
C],
        ], [
            'Initial end phase rules',
            'Added Blighted, timing note, VP scoring details, between-turns cleanup',
        ]);

        // ── Page 9: "Victory & Scoring" — 1 version ───────────────────

        $this->command->info('  Page: Victory & Scoring (1 version)');

        $pages['victory'] = $this->createVersionChain(Page::class, [
            ['title' => 'Victory & Scoring', 'page_number' => 9, 'book_page_numbers' => 'pp. 21-22', 'content' => <<<'C'
{{b}}Victory & Scoring{{/b}}

A game of Malifaux is won by the player who scores the most {{b}}Victory Points (VP){{/b}}. VP are earned through Strategies and Schemes.

{{b}}Strategy{{/b}}

The Strategy is the primary objective, shared by both players. Each Strategy has specific scoring conditions that are checked during the End Phase. A typical Strategy can earn up to {{b}}4 VP{{/b}} over the course of a game.

{{b}}Schemes{{/b}}

Each player secretly selects 2 Schemes from the Scheme pool at the start of the game. Each Scheme can earn up to {{b}}2 VP{{/b}}, for a potential total of {{b}}4 VP{{/b}} from Schemes.

{{b}}Maximum VP{{/b}}

The maximum VP in a standard game is {{b}}8 VP{{/b}} (4 from Strategy + 4 from Schemes).

{{hr /}}

{{b}}Scheme Markers{{/b}}

Many Schemes require placing or interacting with {{b}}Scheme Markers{{/b}}:
- Placed using the {{b}}Interact{{/b}} Action
- A model {{u}}cannot{{/u}} place a Scheme Marker within 4" of another friendly Scheme Marker
- Scheme Markers are {{b}}30mm base size{{/b}}
- An enemy model in base contact may remove a Scheme Marker using the Interact Action

{{b}}Revealing Schemes{{/b}}

Some Schemes require being {{b}}revealed{{/b}} to score VP. A Scheme is revealed by declaring it to your opponent at the specified time (usually the End Phase). Once revealed, a Scheme cannot be hidden again.

{{hr /}}

{{lg}}{{b}}Scoring Summary{{/b}}{{/lg}}

{{sm}}Turn 1: Strategy (0-1 VP), Schemes (0-2 VP){{/sm}}
{{sm}}Turn 2: Strategy (0-1 VP), Schemes (0-2 VP){{/sm}}
{{sm}}Turn 3: Strategy (0-1 VP), Schemes (0-2 VP){{/sm}}
{{sm}}Turn 4: Strategy (0-1 VP), Schemes (0-2 VP){{/sm}}
{{sm}}Turn 5: Strategy (0-1 VP), Schemes (0-2 VP){{/sm}}
{{sm}}{{b}}Maximum: 8 VP{{/b}}{{/sm}}
C],
        ], ['Initial victory and scoring rules']);

        // ── Page 10: "Advanced Rules" — 4 versions ─────────────────────

        $this->command->info('  Page: Advanced Rules (4 versions)');

        $pages['advanced'] = $this->createVersionChain(Page::class, [
            ['title' => 'Advanced Rules', 'page_number' => 10, 'book_page_numbers' => 'pp. 23-24', 'content' => <<<'C'
{{b}}Advanced Rules{{/b}}

These rules expand upon the core mechanics for experienced players.

{{b}}Summoning{{/b}}

Some models can Summon new models during the game. Summoned models:
- Are placed within the specified range
- Gain the {{b}}Slow{{/b}} Condition
- Come into play with full Health
C],
            ['title' => 'Advanced Rules', 'page_number' => 10, 'book_page_numbers' => 'pp. 23-25', 'content' => <<<'C'
{{b}}Advanced Rules{{/b}}

These rules expand upon the core mechanics for experienced players.

{{b}}Summoning{{/b}}

Some models can Summon new models during the game. Summoned models:
- Are placed within the specified range
- Gain the {{b}}Slow{{/b}} Condition
- Come into play with full Health
- {{u}}Cannot{{/u}} Activate the Turn they are Summoned

{{b}}Attaching Upgrades{{/b}}

Upgrades are additional stat cards that can be attached to specific models during hiring. Each Upgrade has a {{soulstone /}} cost and lists which models may take it. A model may only have {{b}}one Upgrade{{/b}} attached at a time unless otherwise stated.
C],
            ['title' => 'Advanced Rules', 'page_number' => 10, 'book_page_numbers' => 'pp. 23-26', 'content' => <<<'C'
{{b}}Advanced Rules{{/b}}

These rules expand upon the core mechanics for experienced players.

{{b}}Summoning{{/b}}

Some models can Summon new models during the game. Summoned models:
- Are placed within the specified range
- Gain the {{b}}Slow{{/b}} Condition
- Come into play with full Health
- {{u}}Cannot{{/u}} Activate the Turn they are Summoned
- Are removed from the game if killed (they do not drop Markers)

{{b}}Attaching Upgrades{{/b}}

Upgrades are additional stat cards that can be attached to specific models during hiring. Each Upgrade has a {{soulstone /}} cost and lists which models may take it.

{{b}}Replacing Models{{/b}}

When an effect says to "Replace" a model with another, the new model:
- Is placed in base contact with the original model's position
- Retains all Conditions and Tokens from the original
- Has Health equal to the original's remaining Health {{sm}}(capped at the new model's max){{/sm}}
- Counts as having Activated if the original already Activated this Turn
C],
            ['title' => 'Advanced Rules', 'page_number' => 10, 'book_page_numbers' => 'pp. 23-27', 'content' => <<<'C'
{{xl}}{{b}}Advanced Rules{{/b}}{{/xl}}

These rules expand upon the core mechanics for experienced players.

{{hr /}}

{{lg}}{{b}}Summoning{{/b}}{{/lg}}

Some models can Summon new models during the game. Summoned models:
- Are placed within the specified range
- Gain the {{b}}Slow{{/b}} Condition
- Come into play with full Health
- {{u}}Cannot{{/u}} Activate the Turn they are Summoned
- Are removed from the game if killed (they do not drop Markers)

{{sm}}{{i}}Note: Summoned models still count as "friendly models" for all game purposes, including Scheme scoring.{{/i}}{{/sm}}

{{hr /}}

{{lg}}{{b}}Attaching Upgrades{{/b}}{{/lg}}

Upgrades are additional stat cards attached during hiring. Each Upgrade has a {{soulstone /}} cost and lists which models may take it.

{{hr /}}

{{lg}}{{b}}Replacing Models{{/b}}{{/lg}}

When an effect says to "Replace" a model:
- New model is placed in base contact with the original's position
- Retains all Conditions and Tokens from the original
- Has Health equal to the original's remaining Health {{sm}}(capped at new max){{/sm}}
- Counts as having Activated if the original already Activated

{{hr /}}

{{lg}}{{b}}{{positive /}} Auras{{/b}}{{/lg}}

An Aura is an ongoing effect centered on a model. Auras have a range (e.g., {{b}}Aura 6"{{/b}}) and affect all models within that range. Auras:
- Are always active while the generating model is in play
- Affect both friendly and enemy models unless specified otherwise
- Stack with other Auras unless they are the same named Aura

{{hr /}}

{{lg}}{{b}}{{pulse /}} Pulses{{/b}}{{/lg}}

A Pulse is a one-time effect that radiates from a point. Pulses have a range and affect all models within that range when the Pulse occurs:
- {{pulse /}} {{b}}Shockwave{{/b}}: Each model in the area must pass a TN duel or suffer the listed effects
- {{pulse /}} {{b}}Healing Pulse{{/b}}: Each friendly model in the area heals the listed amount

{{hr /}}

{{lg}}{{b}}Demise Abilities{{/b}}{{/lg}}

Some models have {{b}}Demise{{/b}} abilities that trigger when the model is killed. Common examples:
- {{b}}Demise (Eternal){{/b}}: This model is not killed. Instead, heal it to full and bury it.
- {{b}}Demise (Explosive +X){{/b}} {{ram /}}: When killed, every model within {{pulse /}} 2" suffers X damage.

{{hr /}}

{{lg}}{{b}}Bury & Unbury{{/b}}{{/lg}}

A Buried model is removed from the table but {{u}}not{{/u}} killed. Buried models:
- Cannot be targeted, moved, or affected by game effects
- Do not count as "in play" for auras or pulses
- May return to play via {{b}}Unbury{{/b}} effects — placed in base contact with a specified model or location
C],
        ], [
            'Initial advanced rules — summoning only',
            'Added upgrade attachment rules',
            'Added model replacement rules',
            'Major expansion: auras, pulses, demise, bury/unbury mechanics',
        ]);

        return $pages;
    }

    // ═════════════════════════════════════════════════════════════
    //  SECTIONS
    // ═════════════════════════════════════════════════════════════

    private function seedSections(): array
    {
        $sections = [];

        // ── Section 1: "Actions" — 2 versions ─────────────────────────

        $this->command->info('  Section: Actions (2 versions)');

        $sections['actions'] = $this->createVersionChain(Section::class, [
            [
                'title' => 'Actions',
                'left_column' => <<<'C'
{{b}}Taking Actions{{/b}}

During its Activation, a model generates Action Points (AP) to spend on Actions.

{{b}}Attack Actions{{/b}}

Attack Actions target enemy models and require a duel:
- {{melee /}} {{b}}Melee{{/b}}: Targets within Engagement Range
- {{missile /}} {{b}}Ranged{{/b}}: Targets within the listed range

{{b}}Tactical Actions{{/b}}

Tactical Actions do not target enemy models. They include movement, support, and interactions.
C,
                'right_column' => <<<'C'
{{b}}Action Modifiers{{/b}}

{{b}}Once Per Activation{{/b}}

This Action can only be taken once during a single Activation.

{{b}}Free Action{{/b}}

Free Actions do not cost AP. Each specific Free Action can only be taken once per Activation.

{{b}}Triggers{{/b}}

Triggers activate when specific suits appear:
- {{crow /}} Crow
- {{ram /}} Ram
- {{tome /}} Tome
- {{mask /}} Mask
C,
            ],
            [
                'title' => 'Actions',
                'left_column' => <<<'C'
{{b}}Taking Actions{{/b}}

During its Activation, a model generates Action Points (AP) to spend on Actions.

{{b}}Attack Actions{{/b}}

Attack Actions target enemy models and require a duel:
- {{melee /}} {{b}}Melee{{/b}}: Targets within Engagement Range (typically 1")
- {{missile /}} {{b}}Ranged{{/b}}: Targets within range, requires Line of Sight

{{b}}Tactical Actions{{/b}}

Tactical Actions do not target enemy models. They include movement, support, and interactions.

{{b}}Action Timing{{/b}}

Actions resolve in order:
1. Declare Action and target(s)
2. Determine the duel
3. Determine success or failure
4. Resolve effects and damage
5. Resolve declared Triggers
C,
                'right_column' => <<<'C'
{{b}}Action Modifiers{{/b}}

{{b}}Once Per Activation{{/b}}

This Action can only be taken once during a single Activation.

{{b}}Free Action{{/b}}

Free Actions do not cost AP. Each specific Free Action can only be taken once per Activation.

{{b}}Triggers{{/b}}

Triggers activate when suits appear in the duel total:
- {{crow /}} {{b}}Crow{{/b}} — Death, decay, and the Resurrectionists
- {{ram /}} {{b}}Ram{{/b}} — Brute force and resilience
- {{tome /}} {{b}}Tome{{/b}} — Magic and arcane knowledge
- {{mask /}} {{b}}Mask{{/b}} — Trickery, speed, and deception

{{b}}Declaring Triggers{{/b}}

After the final duel total is determined, the Acting model may declare one Trigger. Built-in suits (printed on the stat card) are always included.
C,
            ],
        ], [
            'Initial actions section',
            'Added action timing, expanded trigger descriptions',
        ]);

        // ── Section 2: "Conditions" — 1 version ───────────────────────

        $this->command->info('  Section: Conditions (1 version)');

        $sections['conditions'] = $this->createVersionChain(Section::class, [
            [
                'title' => 'Conditions',
                'left_column' => <<<'C'
{{b}}Conditions{{/b}}

Conditions are persistent effects on a model until removed or expired.

{{b}}Burning +X{{/b}}

At end of Turn, suffer X damage, then reduce Burning by 1.

{{b}}Poison +X{{/b}}

At end of Turn, suffer 1 damage per full 3 Poison, then reduce by 1.

{{b}}Slow{{/b}}

Generates 1 fewer AP next Activation. Removed after Activation.

{{b}}Fast{{/b}}

Generates 1 additional AP next Activation. Removed after Activation. Fast and Slow cancel each other out.
C,
                'right_column' => <<<'C'
{{b}}Additional Conditions{{/b}}

{{b}}Focused +X{{/b}} {{positive /}}

Remove to add a {{positive /}} to a duel. Reduced by 1 each use.

{{b}}Stunned{{/b}}

Cannot declare Triggers or take the Interact Action. Removed end of next Activation.

{{b}}Distracted +X{{/b}} {{negative /}}

Suffers {{negative /}} to Attack Actions. Reduced by 1 each time applied.

{{b}}Injured +X{{/b}}

This model's duel totals are reduced by X. Reduced by 1 at end of each Activation.

{{b}}Shielded +X{{/b}}

Reduces damage suffered by X, then the Shielded value is reduced by the damage prevented. Removed at end of Turn.

{{hr /}}

{{b}}Stacking{{/b}}

Conditions with +X stack additively. Conditions without a value (Slow, Fast, Stunned) do not stack.
C,
            ],
        ], ['Initial conditions section']);

        // ── Section 3: "Duels" — 2 versions ───────────────────────────

        $this->command->info('  Section: Duels (2 versions)');

        $sections['duels'] = $this->createVersionChain(Section::class, [
            [
                'title' => 'Duels',
                'left_column' => <<<'C'
{{b}}Duels{{/b}}

Duels are the primary resolution mechanic.

{{b}}Simple Duels{{/b}}

Made against a fixed Target Number (TN). Flip a card and add the relevant stat. Meet or exceed the TN to succeed.

{{b}}Opposed Duels{{/b}}

Both players flip and add stats. Higher total wins. Ties go to the defender.

{{b}}The Duel Process{{/b}}

1. Attacker flips a Fate Card
2. Defender flips a Fate Card
3. Both may Cheat Fate
4. Compare totals
C,
                'right_column' => <<<'C'
{{b}}Cheating Fate{{/b}}

Replace a flipped card with one from your Control Hand.

{{b}}Jokers{{/b}}

- {{b}}Red Joker{{/b}}: Value 14, any suit. Cannot be cheated.
- {{b}}Black Joker{{/b}}: Value 0, no suit. Auto-fails. Cannot be cheated.

{{b}}Modifiers{{/b}}

- {{positive /}} {{b}}Positive Twist{{/b}}: Flip extra, keep highest
- {{negative /}} {{b}}Negative Twist{{/b}}: Flip extra, opponent keeps lowest
C,
            ],
            [
                'title' => 'Duels',
                'left_column' => <<<'C'
{{b}}Duels{{/b}}

Duels are the primary resolution mechanic.

{{b}}Simple Duels{{/b}}

Made against a fixed Target Number (TN). Flip a card and add the relevant stat. Meet or exceed the TN to succeed.

{{b}}Opposed Duels (Clarified){{/b}}

1. {{b}}Attacker Flips{{/b}}: Flip and add the Action's stat
2. {{b}}Defender Flips{{/b}}: Flip and add the defensive stat
3. {{b}}Cheat Fate{{/b}}: {{b}}Non-Active player{{/b}} cheats first, then Active player
4. {{b}}Compare Totals{{/b}}: Higher wins. Ties go to the {{b}}defender{{/b}}
5. {{b}}Declare Triggers{{/b}}: Winning model may declare applicable Triggers

{{b}}Important{{/b}}: On a {{negative /}} Negative Twist, a model {{u}}cannot{{/u}} Cheat Fate unless it has an appropriate Ability.
C,
                'right_column' => <<<'C'
{{b}}Cheating Fate{{/b}}

Replace a flipped card with one from your Control Hand.

{{b}}Jokers{{/b}}

- {{b}}Red Joker{{/b}}: Value 14, any suit. Cannot be cheated.
- {{b}}Black Joker{{/b}}: Value 0, no suit. Auto-fails. Cannot be cheated.

{{b}}Modifiers{{/b}}

- {{positive /}} {{b}}Positive Twist{{/b}}: Flip one extra card, keep the highest value
- {{negative /}} {{b}}Negative Twist{{/b}}: Flip one extra card, your opponent chooses which you use

{{b}}Multiple Modifiers{{/b}}: {{positive /}} and {{negative /}} cancel one-for-one. Multiple {{positive /}} cap at double-positive (flip 2 extra, keep best).
C,
            ],
        ], [
            'Initial duels section',
            'Clarified opposed duel order, cheat restrictions, multiple modifiers',
        ]);

        // ── Section 4: "Damage & Healing" — 2 versions (symbols in title) ─

        $this->command->info('  Section: Damage & Healing (2 versions)');

        $sections['damage'] = $this->createVersionChain(Section::class, [
            [
                'title' => '{{positive /}} Damage & {{negative /}} Healing',
                'left_column' => <<<'C'
{{b}}{{positive /}} Dealing Damage{{/b}}

When an Attack Action succeeds, the attacker flips a card to determine damage from the Action's damage profile ({{b}}Weak / Moderate / Severe{{/b}}).

{{b}}Damage Flip{{/b}}

The flipped card's value determines the severity:
- {{b}}1-5{{/b}}: Weak damage
- {{b}}6-10{{/b}}: Moderate damage
- {{b}}11-14{{/b}}: Severe damage
- {{b}}Red Joker{{/b}}: Always Severe + 1 additional damage
- {{b}}Black Joker{{/b}}: Always Weak damage (even with {{positive /}})

Damage flips can be modified by {{positive /}} and {{negative /}} twists.

{{b}}Armor{{/b}}

Models with {{b}}Armor +X{{/b}} reduce all damage suffered by X (minimum 1). Armor applies after all other damage modifications.
C,
                'right_column' => <<<'C'
{{b}}{{negative /}} Healing{{/b}}

Healing restores lost Health to a model. A model {{u}}cannot{{/u}} be healed above its maximum Health.

{{b}}Sources of Healing{{/b}}

- Actions with the {{b}}Heal{{/b}} keyword
- Certain Triggers (e.g., {{crow /}} {{b}}Drain Life{{/b}})
- Abilities that heal at specific timings

{{b}}Hard to Kill{{/b}}

When a model with {{b}}Hard to Kill{{/b}} would be reduced to 0 Health, it is instead reduced to 1 Health. This triggers once per Activation.

{{b}}Killed Models{{/b}}

When a model is reduced to 0 Health, it is {{b}}killed{{/b}} and removed from play. Some models have {{b}}Demise{{/b}} abilities that trigger when killed.
C,
            ],
            [
                'title' => '{{positive /}} Damage & {{negative /}} Healing',
                'left_column' => <<<'C'
{{b}}{{positive /}} Dealing Damage{{/b}}

When an Attack Action succeeds, the attacker flips a card to determine damage from the Action's damage profile ({{b}}Weak / Moderate / Severe{{/b}}).

{{b}}Damage Flip{{/b}}

The flipped card determines severity:
- {{b}}1-5{{/b}}: Weak damage
- {{b}}6-10{{/b}}: Moderate damage
- {{b}}11-14{{/b}}: Severe damage
- {{b}}Red Joker{{/b}}: Always Severe + 1
- {{b}}Black Joker{{/b}}: Always Weak (even with {{positive /}})

{{b}}Armor +X{{/b}}

Reduce all damage by X (minimum 1). Applies after all other modifications.

{{b}}{{ram /}} Hard to Wound{{/b}}

Damage flips against this model receive {{negative /}}. Combined with {{b}}Armor{{/b}}, this makes a model very durable.

{{b}}Irreducible Damage{{/b}}

Some effects deal {{b}}irreducible damage{{/b}} — this damage {{u}}cannot{{/u}} be reduced by Armor, Shielded, {{soulstone /}} Soulstones, or any other effect.
C,
                'right_column' => <<<'C'
{{b}}{{negative /}} Healing{{/b}}

Healing restores lost Health. A model {{u}}cannot{{/u}} exceed its maximum Health.

{{b}}Sources of Healing{{/b}}

- Actions with the {{b}}Heal{{/b}} keyword
- Triggers (e.g., {{crow /}} {{b}}Drain Life{{/b}}, {{tome /}} {{b}}Mend{{/b}})
- Abilities at specific timings
- {{soulstone /}} Soulstone use (some Masters)

{{b}}Hard to Kill{{/b}}

Reduced to 1 Health instead of 0. Once per Activation only.

{{b}}Killed Models{{/b}}

At 0 Health a model is killed and removed. {{b}}Demise{{/b}} abilities trigger when killed:
- {{b}}Demise (Eternal){{/b}}: Heal to full and bury instead
- {{b}}Demise (Explosive +X){{/b}} {{ram /}}: {{pulse /}} 2" — X damage to nearby models

{{b}}Markers from Death{{/b}}

Certain killed models drop markers:
- {{b}}Corpse Marker{{/b}}: Left by most living models — used by Resurrectionists
- {{b}}Scrap Marker{{/b}}: Left by Constructs — used by Arcanists and Gremlins
C,
            ],
        ], [
            'Initial damage and healing rules',
            'Added Hard to Wound, irreducible damage, death markers, more trigger examples',
        ]);

        // ── Section 5: "The Undead" — 1 version (symbol in title, left only) ─

        $this->command->info('  Section: The Undead (1 version, left column only)');

        $sections['undead'] = $this->createVersionChain(Section::class, [
            [
                'title' => '{{crow /}} The Undead',
                'left_column' => <<<'C'
{{xl}}{{b}}{{crow /}} The Undead{{/b}}{{/xl}}

The Undead are a common enemy type in Malifaux, primarily associated with the {{b}}Resurrectionists{{/b}} faction. Undead models share several common traits.

{{b}}Common Undead Traits{{/b}}

{{b}}Hard to Kill{{/b}}
Most Undead models have this ability, representing their unnatural resilience.

{{b}}Terrifying (X){{/b}} {{fortitude /}}
Many powerful Undead cause terror in the living. Enemy models must pass a {{fortitude /}} Willpower duel when targeting or approaching.

{{b}}Undead{{/b}}
The {{b}}Undead{{/b}} characteristic means:
- Immune to {{b}}Poison{{/b}} (they have no living biology)
- Cannot be healed by normal means (require specific necromantic healing)
- Affected by abilities that specifically target Undead models

{{b}}{{crow /}} Necromancy{{/b}}

Many Resurrectionist Actions require the {{crow /}} suit. This thematic connection to death and decay means:
- {{crow /}} {{b}}Reanimate{{/b}}: Summon a new Undead model from a Corpse Marker
- {{crow /}} {{crow /}} {{b}}Dark Resurrection{{/b}}: Summon a powerful Undead from multiple Corpse Markers
- {{crow /}} {{b}}Decay{{/b}}: Deal damage and heal the caster

{{hr /}}

{{sm}}{{i}}Lore Note: The Undead of Malifaux are created when Soulstone-infused {{magic /}} magical energy reanimates corpses. Unlike traditional fantasy undead, Malifaux's Undead retain varying degrees of intelligence depending on the power of the necromancer who created them.{{/i}}{{/sm}}
C,
                'right_column' => '',
            ],
        ], ['Initial undead reference section']);

        // ── Section 6: "Markers & Tokens" — 3 versions ────────────────

        $this->command->info('  Section: Markers & Tokens (3 versions)');

        $sections['markers'] = $this->createVersionChain(Section::class, [
            [
                'title' => 'Markers & Tokens',
                'left_column' => <<<'C'
{{b}}Markers{{/b}}

Markers are game elements placed on the table.

{{b}}Scheme Markers{{/b}}

Placed by the Interact Action. Used for scoring Schemes. 30mm base. Cannot be placed within 4" of another friendly Scheme Marker.

{{b}}Strategy Markers{{/b}}

Placed as part of the Strategy setup. Specific rules vary by Strategy.

{{b}}Corpse Markers{{/b}}

Dropped when most living models are killed. 30mm base. Used by Resurrectionists and certain other models.

{{b}}Scrap Markers{{/b}}

Dropped when Construct models are killed. 30mm base. Used by Arcanists and certain other models.
C,
                'right_column' => <<<'C'
{{b}}Tokens{{/b}}

Tokens track ongoing effects on models.

{{b}}Condition Tokens{{/b}}

Used to track Conditions like Burning, Poison, Focused, etc. Place the appropriate number of tokens near the model.

{{b}}Activation Tokens{{/b}}

Place an Activation Token next to a model after it Activates to track which models have already acted this Turn.
C,
            ],
            [
                'title' => 'Markers & Tokens',
                'left_column' => <<<'C'
{{b}}Markers{{/b}}

Markers are game elements placed on the table. All markers are {{b}}30mm base size{{/b}} unless otherwise stated.

{{b}}Scheme Markers{{/b}}

Placed via Interact Action. Used for Scheme scoring.
- Cannot be placed within {{b}}4"{{/b}} of another friendly Scheme Marker
- Enemy models in base contact may remove with Interact
- {{u}}Cannot{{/u}} be placed while Engaged

{{b}}Strategy Markers{{/b}}

Placed as part of Strategy setup. Rules vary by Strategy. Strategy Markers are {{u}}not{{/u}} Scheme Markers.

{{b}}Corpse Markers{{/b}}

Dropped when living (non-Construct) models are killed. {{crow /}} Resurrectionists use Corpse Markers for summoning and healing.

{{b}}Scrap Markers{{/b}}

Dropped when Construct models are killed. Used by Arcanists ({{b}}Augmented{{/b}} keyword) and Gremlins for various abilities.
C,
                'right_column' => <<<'C'
{{b}}Tokens{{/b}}

Tokens track ongoing effects on models. We recommend using numbered tokens or dice for Conditions with +X values.

{{b}}Condition Tokens{{/b}}

Track Conditions: Burning, Poison, Focused {{positive /}}, Distracted {{negative /}}, Injured, Shielded, etc.

{{b}}Activation Tokens{{/b}}

Track which models have Activated this Turn.

{{b}}Pass Tokens{{/b}}

In some tournament formats, a player who Passes receives a Pass Token. If a player accumulates 2 Pass Tokens, they must activate a model instead of Passing.
C,
            ],
            [
                'title' => 'Markers & Tokens',
                'left_column' => <<<'C'
{{b}}Markers{{/b}}

Markers are game elements placed on the table. All markers are {{b}}30mm base size{{/b}} unless otherwise stated.

{{hr /}}

{{b}}Scheme Markers{{/b}}

Placed via Interact Action. Used for Scheme scoring.
- Cannot be placed within {{b}}4"{{/b}} of another friendly Scheme Marker
- Enemy models in base contact may remove with Interact
- {{u}}Cannot{{/u}} be placed while Engaged

{{b}}Strategy Markers{{/b}}

Placed during Strategy setup. Rules vary by Strategy. {{u}}Not{{/u}} Scheme Markers.

{{b}}Corpse Markers{{/b}} {{crow /}}

Dropped by killed living models. Used by {{crow /}} Resurrectionists for summoning and healing.

{{b}}Scrap Markers{{/b}}

Dropped by killed Constructs. Used by Arcanists and Gremlins.

{{b}}Pyre Markers{{/b}}

50mm markers that count as {{b}}Hazardous{{/b}} and {{b}}Severe{{/b}} terrain. Models moving through or ending in a Pyre Marker gain {{b}}Burning +1{{/b}}. Created by Kaeris and Wildfire models.

{{b}}Ice Pillars{{/b}}

Blocking, Impassable terrain markers (Ht 5, 30mm). Created by Rasputina and December models. Can be destroyed (Df 4, 3 Wd).
C,
                'right_column' => <<<'C'
{{b}}Tokens{{/b}}

Tokens track ongoing effects. We recommend numbered tokens or dice for +X values.

{{hr /}}

{{b}}Condition Tokens{{/b}}

Track Conditions: Burning, Poison, Focused {{positive /}}, Distracted {{negative /}}, Injured, Shielded, Staggered, etc.

{{b}}Activation Tokens{{/b}}

Track which models have Activated this Turn.

{{b}}Pass Tokens{{/b}}

Tournament rule: a player who Passes receives a Pass Token. At 2 Pass Tokens, they must Activate instead.

{{hr /}}

{{b}}Summary of Markers{{/b}}

{{sm}}| Marker | Size | Dropped By | Used By |{{/sm}}
{{sm}}| Scheme | 30mm | Interact Action | Scheme scoring |{{/sm}}
{{sm}}| Corpse | 30mm | Killed living models | Resurrectionists |{{/sm}}
{{sm}}| Scrap | 30mm | Killed Constructs | Arcanists, Gremlins |{{/sm}}
{{sm}}| Pyre | 50mm | Wildfire Actions | Hazardous terrain |{{/sm}}
{{sm}}| Ice Pillar | 30mm | December Actions | Blocking terrain |{{/sm}}
C,
            ],
        ], [
            'Initial markers and tokens',
            'Added placement restrictions, Pass Tokens',
            'Added Pyre Markers, Ice Pillars, summary table',
        ]);

        // ── Section 7: "Auras & Pulses" — 2 versions (symbols in title) ─

        $this->command->info('  Section: Auras & Pulses (2 versions)');

        $sections['auras'] = $this->createVersionChain(Section::class, [
            [
                'title' => 'Auras & {{pulse /}} Pulses',
                'left_column' => <<<'C'
{{b}}Auras{{/b}}

An Aura is an ongoing effect centered on a model with a specified range. Auras affect all models within range while the generating model is in play.

{{b}}Example Auras:{{/b}}

{{b}}Aura of Decay ({{crow /}} Aura 3"){{/b}}
Enemy models within range suffer {{negative /}} to healing flips.

{{b}}Protective Aura (Aura 6"){{/b}}
Friendly models within range gain {{b}}+1 Armor{{/b}}.

{{b}}Terrifying Aura ({{fortitude /}} Aura 4"){{/b}}
Enemy models entering the Aura must pass a TN 12 {{fortitude /}} Willpower duel or gain Stunned.
C,
                'right_column' => <<<'C'
{{b}}{{pulse /}} Pulses{{/b}}

A Pulse is a one-time effect radiating from a point. All models within range are affected.

{{b}}Shockwaves{{/b}} {{pulse /}}

A Shockwave is a specific type of Pulse. When a Shockwave occurs:
1. Place a 50mm Shockwave Marker at the target point
2. Every model touching the marker must pass a TN duel
3. Models that fail suffer the listed effects

{{b}}Example Pulses:{{/b}}

{{b}}{{pulse /}} 3" — Burning +2{{/b}}
All models within 3" gain Burning +2.

{{b}}{{pulse /}} 6" — Heal 2{{/b}}
All friendly models within 6" heal 2 damage.
C,
            ],
            [
                'title' => 'Auras & {{pulse /}} Pulses',
                'left_column' => <<<'C'
{{b}}Auras{{/b}}

An Aura is an ongoing effect centered on a model. Auras have a range and affect all qualifying models within that range.

{{b}}Aura Rules:{{/b}}
- Active while the generating model is in play and not Buried
- Affect both friendly and enemy models unless specified
- Same-named Auras do {{u}}not{{/u}} stack

{{b}}Example Auras:{{/b}}

{{b}}{{crow /}} Aura of Decay (Aura 3"){{/b}}
Enemy models within range suffer {{negative /}} to healing flips.

{{b}}Protective Aura (Aura 6"){{/b}}
Friendly models gain {{b}}+1 Armor{{/b}}.

{{b}}{{fortitude /}} Terrifying Aura (Aura 4"){{/b}}
Enemy models entering must pass TN 12 {{fortitude /}} duel or gain Stunned.

{{b}}{{magic /}} Arcane Aura (Aura 8"){{/b}}
Friendly {{tome /}} Actions within range gain {{positive /}} to the casting duel.
C,
                'right_column' => <<<'C'
{{b}}{{pulse /}} Pulses{{/b}}

A Pulse is a one-time effect radiating from a point. All models within range when the Pulse occurs are affected.

{{b}}Shockwaves{{/b}} {{pulse /}}

A special Pulse type:
1. Place a 50mm Shockwave Marker at target point
2. Every model touching the marker must pass a TN duel
3. Models that fail suffer the listed effects
4. Remove the Shockwave Marker

{{sm}}{{i}}Note: Shockwaves are not Attack Actions — they cannot be Cheated and do not trigger Defensive abilities.{{/i}}{{/sm}}

{{b}}Example Pulses:{{/b}}

{{b}}{{pulse /}} 3" — Burning +2{{/b}}
All models within 3" gain Burning +2.

{{b}}{{pulse /}} 6" — Heal 2{{/b}}
All friendly models within 6" heal 2.

{{b}}{{pulse /}} 2" — {{ram /}} Stagger{{/b}}
All enemy models within 2" gain Staggered.

{{hr /}}

{{b}}Blasts{{/b}} {{pulse /}}

Some damage results include {{b}}Blast{{/b}} markers ({{pulse /}} symbols on the damage profile):
- {{pulse /}} = Place a 30mm Blast marker adjacent to the target
- {{pulse /}} {{pulse /}} = Place two Blast markers
- All models under Blast markers suffer the listed blast damage
C,
            ],
        ], [
            'Initial auras and pulses section',
            'Added aura stacking rules, Arcane Aura, Shockwave details, Blasts',
        ]);

        // ── Section 8: "Signature Actions" — 1 version (symbol in title) ─

        $this->command->info('  Section: Signature Actions (1 version)');

        $sections['signature'] = $this->createVersionChain(Section::class, [
            [
                'title' => '{{signatureaction /}} Signature Actions',
                'left_column' => <<<'C'
{{xl}}{{b}}{{signatureaction /}} Signature Actions{{/b}}{{/xl}}

Signature Actions are unique, powerful Actions available only to specific Masters. They define a Master's primary combat capability and playstyle.

{{b}}Rules for Signature Actions{{/b}}

- Only the named Master may take this Action
- {{signatureaction /}} Signature Actions are always {{b}}Attack Actions{{/b}}
- They follow all normal Attack Action rules

{{b}}Examples{{/b}}

{{hr /}}

{{b}}{{signatureaction /}} Greatsword{{/b}} {{melee /}}
{{sm}}{{i}}Lady Justice — Stat 7 {{melee /}} — Rst: Df — Damage: 3/4/6{{/i}}{{/sm}}
{{b}}Triggers:{{/b}}
- {{crow /}} {{b}}Onslaught{{/b}}: After succeeding, take this Action again (once per Activation)
- {{ram /}} {{b}}Critical Strike{{/b}}: +1 damage per {{ram /}} in the final duel total

{{hr /}}

{{b}}{{signatureaction /}} Obey{{/b}} {{magic /}}
{{sm}}{{i}}Zoraida — Stat 7 {{magic /}} — Rst: Wp — Range 12"{{/i}}{{/sm}}
Target a model — it immediately takes a non-{{signatureaction /}} Action controlled by this model's controller.
{{b}}Triggers:{{/b}}
- {{mask /}} {{b}}My Bidding{{/b}}: The target may take a second Action

{{hr /}}

{{b}}{{signatureaction /}} The Whisper{{/b}} {{crow /}}
{{sm}}{{i}}Pandora — Stat 7 {{crow /}} — Rst: Wp — Range 8"{{/i}}{{/sm}}
Target suffers 2/3/5 damage. The target then must discard a card or gain Stunned.
{{b}}Triggers:{{/b}}
- {{crow /}} {{mask /}} {{b}}Misery Loves Company{{/b}}: After damaging, every enemy model within {{pulse /}} 3" of the target suffers 1 damage
C,
                'right_column' => <<<'C'
{{b}}More Examples{{/b}}

{{hr /}}

{{b}}{{signatureaction /}} Shockwave Bolt{{/b}} {{missile /}}
{{sm}}{{i}}Rasputina — Stat 6 {{tome /}} — Range 14"{{/i}}{{/sm}}
Place a 50mm Shockwave Marker. Models touching must pass TN 13 {{fortitude /}} or suffer 2 damage and gain {{b}}Slow{{/b}}.
{{b}}Triggers:{{/b}}
- {{tome /}} {{b}}Overpower{{/b}}: Target also gains Staggered
- {{tome /}} {{mask /}} {{b}}Ice Mirror{{/b}}: May draw LoS from a friendly Ice Pillar instead

{{hr /}}

{{b}}{{signatureaction /}} Flurry of Blades{{/b}} {{melee /}}
{{sm}}{{i}}Viktoria of Ashes — Stat 7 {{melee /}} — Rst: Df — Damage: 2/4/5{{/i}}{{/sm}}
After resolving, take this Action again against a different target within {{melee /}} range {{sm}}(once per Activation){{/sm}}.
{{b}}Triggers:{{/b}}
- {{ram /}} {{b}}Whirlwind{{/b}}: All enemy models within 2" suffer 1 damage
- {{mask /}} {{b}}Fade Away{{/b}}: After resolving, this model may Push 3"

{{hr /}}

{{sm}}{{i}}{{b}}Design Note:{{/b}} Signature Actions are deliberately powerful to make Masters feel impactful on the table. They are balanced by the Master's Soulstone cost and the opportunity cost of hiring other models.{{/i}}{{/sm}}
C,
            ],
        ], ['Initial signature actions section']);

        return $sections;
    }

    // ═════════════════════════════════════════════════════════════
    //  INDICES
    // ═════════════════════════════════════════════════════════════

    private function seedIndices(): array
    {
        $indices = [];

        // ── Index 1: "Abilities Index" — 2 versions ────────────────────

        $this->command->info('  Index: Abilities Index (2 versions)');

        $indices['abilities'] = $this->createVersionChain(Index::class, [
            ['title' => 'Abilities Index', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}Abilities Index{{/b}}

Common Abilities found across multiple models.

{{b}}Armor +X{{/b}}
Reduce all damage by +X (minimum 1).

{{b}}Hard to Kill{{/b}}
Reduced to 1 Health instead of 0. Once per Activation.

{{b}}Flight{{/b}}
Ignore terrain and models when moving.

{{b}}Ruthless{{/b}}
Ignore Terrifying effects.

{{b}}Terrifying (X){{/b}} {{fortitude /}}
Enemy models must pass TN X {{fortitude /}} duel when targeting or entering engagement range.

{{b}}Unimpeded{{/b}}
Ignore Severe Terrain movement penalty.

{{b}}Arcane Shield{{/b}} {{tome /}}
Reduce damage by 1 when spending a {{soulstone /}}.
C],
            ['title' => 'Abilities Index', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}Abilities Index{{/b}}

Common Abilities found across multiple models.

{{b}}Arcane Shield{{/b}} {{tome /}}
Reduce damage by 1 when spending a {{soulstone /}}.

{{b}}Armor +X{{/b}}
Reduce all damage by X (minimum 1).

{{b}}Companion{{/b}}
After an allied model within 6" Activates, this model may Activate immediately.

{{b}}Flight{{/b}}
Ignore terrain and models when moving. May end atop terrain.

{{b}}Gun Fighter{{/b}}
This model may take {{missile /}} Actions while Engaged.

{{b}}Hard to Kill{{/b}}
Reduced to 1 Health instead of 0. Once per Activation.

{{b}}Hard to Wound{{/b}} {{ram /}}
Damage flips against this model receive {{negative /}}.

{{b}}Incorporeal{{/b}}
Ignore terrain when moving. Reduce all damage by half (round up).

{{b}}Manipulative{{/b}}
Enemies must pass TN 12 Wp to target with Attacks before this model Activates.

{{b}}Ruthless{{/b}}
Ignore Terrifying and Manipulative effects.

{{b}}Terrifying (X){{/b}} {{fortitude /}}
Enemy models must pass TN X {{fortitude /}} duel when targeting or entering engagement range.

{{b}}Unimpeded{{/b}}
Ignore Severe Terrain movement penalty.
C],
        ], [
            'Initial abilities index',
            'Alphabetized, added Companion, Gun Fighter, Hard to Wound, Incorporeal, Manipulative',
        ]);

        // ── Index 2: "Keyword Reference" — 1 version ──────────────────

        $this->command->info('  Index: Keyword Reference (1 version)');

        $indices['keywords'] = $this->createVersionChain(Index::class, [
            ['title' => 'Keyword Reference', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}Keyword Reference{{/b}}

Keywords define thematic model groupings. A Master's Keyword determines in-keyword hiring.

{{lg}}{{b}}Guild{{/b}}{{/lg}}
- {{b}}Augmented{{/b}}: Hoffman — constructs and cybernetics
- {{b}}Elite{{/b}}: Dashel — Guild Guard law enforcement
- {{b}}Family{{/b}}: Perdita — Ortega gunfighters
- {{b}}Guard{{/b}}: Lady Justice — Death Marshals and undead hunters
- {{b}}Marshal{{/b}}: Lady Justice (alt) — controlling and imprisoning spirits

{{lg}}{{b}}Arcanists{{/b}}{{/lg}}
- {{b}}Academics{{/b}}: Ironsides — miners and union muscle
- {{b}}December{{/b}}: Rasputina — ice and cold
- {{b}}Wildfire{{/b}}: Kaeris — fire and burning

{{lg}}{{b}}Neverborn{{/b}}{{/lg}}
- {{b}}Nephilim{{/b}}: Lilith — demonic creatures
- {{b}}Woe{{/b}}: Pandora — emotional torment
- {{b}}Nightmare{{/b}}: The Dreamer — nightmare creatures

{{lg}}{{b}}Resurrectionists{{/b}}{{/lg}}
- {{b}}Redchapel{{/b}}: Seamus — undead belles
- {{b}}Experimental{{/b}}: McMourning — medical experiments
- {{b}}Transmortis{{/b}}: Von Schtook — academic undead

{{lg}}{{b}}Outcasts{{/b}}{{/lg}}
- {{b}}Mercenary{{/b}}: Von Schill — soldiers of fortune
- {{b}}Bandit{{/b}}: Parker Barrows — gunslingers and outlaws
- {{b}}Viktorias{{/b}}: Viktoria — twin swordfighters

{{lg}}{{b}}Bayou{{/b}}{{/lg}}
- {{b}}Kin{{/b}}: Ophelia — Gremlin gunfighters
- {{b}}Wizz-Bang{{/b}}: Wong — explosive magic
- {{b}}Pig{{/b}}: Ulix — pig wranglers and war pigs
C],
        ], ['Initial keyword reference']);

        // ── Index 3: "Melee Actions Index" — 2 versions (symbol in title) ─

        $this->command->info('  Index: Melee Actions Index (2 versions)');

        $indices['melee'] = $this->createVersionChain(Index::class, [
            ['title' => '{{melee /}} Melee Actions Index', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}{{melee /}} Melee Actions Index{{/b}}

Common {{melee /}} Melee Actions found across models.

{{hr /}}

{{b}}{{melee /}} Claws{{/b}}
{{sm}}Stat 5 — Rst: Df — Damage: 2/3/4{{/sm}}
Basic melee attack. Found on many beast and undead models.

{{b}}{{melee /}} Sword{{/b}}
{{sm}}Stat 6 — Rst: Df — Damage: 2/4/5{{/sm}}
Standard melee weapon. Found on many Guild and Outcast models.
- {{ram /}} {{b}}Critical Strike{{/b}}: +1 damage per {{ram /}}

{{b}}{{melee /}} Pneumatic Fist{{/b}}
{{sm}}Stat 6 — Rst: Df — Damage: 2/3/5{{/sm}}
Found on Augmented models.
- {{tome /}} {{b}}Overclock{{/b}}: After damaging, target gains Staggered
C],
            ['title' => '{{melee /}} Melee Actions Index', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}{{melee /}} Melee Actions Index{{/b}}

Common {{melee /}} Melee Actions found across models, organized by stat value.

{{hr /}}

{{lg}}Stat 5{{/lg}}

{{b}}{{melee /}} Claws{{/b}}
{{sm}}Stat 5 — Rst: Df — Damage: 2/3/4{{/sm}}
Basic melee attack. Found on beasts and undead.

{{b}}{{melee /}} Knife{{/b}}
{{sm}}Stat 5 — Rst: Df — Damage: 1/2/4{{/sm}}
Basic bladed weapon. Found on Minion-tier models.
- {{mask /}} {{b}}Backstab{{/b}}: +1 damage if target is Engaged with another friendly model

{{hr /}}

{{lg}}Stat 6{{/lg}}

{{b}}{{melee /}} Sword{{/b}}
{{sm}}Stat 6 — Rst: Df — Damage: 2/4/5{{/sm}}
Standard melee weapon.
- {{ram /}} {{b}}Critical Strike{{/b}}: +1 damage per {{ram /}}

{{b}}{{melee /}} Pneumatic Fist{{/b}}
{{sm}}Stat 6 — Rst: Df — Damage: 2/3/5{{/sm}}
Found on Augmented Constructs.
- {{tome /}} {{b}}Overclock{{/b}}: Target gains Staggered

{{b}}{{melee /}} Necromantic Staff{{/b}} {{crow /}}
{{sm}}Stat 6 {{crow /}} — Rst: Df — Damage: 2/3/4{{/sm}}
Found on Resurrectionist Henchmen.
- {{crow /}} {{b}}Drain Life{{/b}}: After damaging, heal 1
- {{crow /}} {{crow /}} {{b}}Siphon Vitality{{/b}}: After damaging, heal 2 and target gains Injured +1

{{hr /}}

{{lg}}Stat 7{{/lg}}

{{b}}{{melee /}} Greatsword{{/b}} {{signatureaction /}}
{{sm}}Stat 7 — Rst: Df — Damage: 3/4/6{{/sm}}
Lady Justice's {{signatureaction /}} Signature Action.
- {{crow /}} {{b}}Onslaught{{/b}}: Take this Action again (once per Activation)
- {{ram /}} {{b}}Critical Strike{{/b}}: +1 damage per {{ram /}}

{{b}}{{melee /}} Wicked Blade{{/b}}
{{sm}}Stat 7 — Rst: Df — Damage: 2/4/6{{/sm}}
High-tier melee attack.
- {{mask /}} {{b}}Fade Away{{/b}}: After resolving, Push 3"
- {{ram /}} {{mask /}} {{b}}Decapitate{{/b}}: If target is killed, it cannot use Demise abilities
C],
        ], [
            'Initial melee actions index',
            'Added Knife, Necromantic Staff, organized by stat, more triggers',
        ]);

        // ── Index 4: "Trigger Reference" — 3 versions (multiple symbols in title)

        $this->command->info('  Index: Trigger Reference (3 versions)');

        $indices['triggers'] = $this->createVersionChain(Index::class, [
            ['title' => 'Trigger Reference {{crow /}}{{ram /}}{{tome /}}{{mask /}}', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}Trigger Reference{{/b}} {{crow /}}{{ram /}}{{tome /}}{{mask /}}

Triggers are special effects on Actions activated by suits in the final duel total.

{{b}}{{crow /}} Crow Triggers{{/b}}

{{b}}{{crow /}} Onslaught{{/b}}: After succeeding, take this Action again (once per Activation).
{{b}}{{crow /}} Drain Life{{/b}}: After damaging, heal 1/2/3.

{{b}}{{ram /}} Ram Triggers{{/b}}

{{b}}{{ram /}} Critical Strike{{/b}}: +1 damage per {{ram /}} in the final duel total.
{{b}}{{ram /}} Knockback{{/b}}: Push the target 2" directly away.

{{b}}{{tome /}} Tome Triggers{{/b}}

{{b}}{{tome /}} Arcane Enhancement{{/b}}: After resolving, draw a card.
{{b}}{{tome /}} Overpower{{/b}}: Target gains a negative Condition.

{{b}}{{mask /}} Mask Triggers{{/b}}

{{b}}{{mask /}} Fade Away{{/b}}: After resolving, this model may Push 3".
{{b}}{{mask /}} Backstab{{/b}}: +1 damage if target is Engaged with another friendly model.
C],
            ['title' => 'Trigger Reference {{crow /}}{{ram /}}{{tome /}}{{mask /}}', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{b}}Trigger Reference{{/b}} {{crow /}}{{ram /}}{{tome /}}{{mask /}}

Triggers are special effects activated by suits in the final duel total. A model may declare one Trigger per Action.

{{lg}}{{b}}{{crow /}} Crow Triggers{{/b}}{{/lg}}

{{b}}{{crow /}} Onslaught{{/b}}: Take this Action again against a different target (once per Activation).
{{b}}{{crow /}} Drain Life{{/b}}: After damaging, heal 1/2/3.
{{b}}{{crow /}} Infect{{/b}}: Target gains Poison +2.
{{b}}{{crow /}} {{crow /}} Dark Fate{{/b}}: Target discards a card or suffers +2 damage.

{{lg}}{{b}}{{ram /}} Ram Triggers{{/b}}{{/lg}}

{{b}}{{ram /}} Critical Strike{{/b}}: +1 damage per {{ram /}} in final total.
{{b}}{{ram /}} Knockback{{/b}}: Push target 2" directly away.
{{b}}{{ram /}} Stagger{{/b}}: Target gains Staggered.
{{b}}{{ram /}} {{ram /}} Crushing Blow{{/b}}: Damage flip receives {{positive /}}.

{{lg}}{{b}}{{tome /}} Tome Triggers{{/b}}{{/lg}}

{{b}}{{tome /}} Arcane Enhancement{{/b}}: After resolving, draw a card.
{{b}}{{tome /}} Overpower{{/b}}: Target gains a negative Condition (model's choice).
{{b}}{{tome /}} Shield of Magic{{/b}}: This model gains Shielded +1.
{{b}}{{tome /}} {{tome /}} Surge{{/b}}: After resolving, take a free Interact Action.

{{lg}}{{b}}{{mask /}} Mask Triggers{{/b}}{{/lg}}

{{b}}{{mask /}} Fade Away{{/b}}: After resolving, Push 3".
{{b}}{{mask /}} Backstab{{/b}}: +1 damage if target is Engaged with another friendly model.
{{b}}{{mask /}} Diversion{{/b}}: After resolving, target cannot take {{melee /}} Actions until its next Activation.
{{b}}{{mask /}} {{mask /}} Vanish{{/b}}: After resolving, this model may Bury.
C],
            ['title' => 'Trigger Reference {{crow /}}{{ram /}}{{tome /}}{{mask /}}', 'type' => IndexTypeEnum::Text, 'content' => <<<'C'
{{xl}}{{b}}Trigger Reference{{/b}} {{crow /}}{{ram /}}{{tome /}}{{mask /}}{{/xl}}

Triggers are special effects activated by suits in the final duel total. A model may declare {{b}}one Trigger per Action{{/b}} unless an Ability says otherwise.

{{b}}Built-in Suits{{/b}}: Suits printed on the stat card are always included. Additional suits can come from:
- The flipped/cheated card's suit
- {{soulstone /}} Soulstone use (Masters/Henchmen only)
- Abilities that add suits

{{hr /}}

{{lg}}{{b}}{{crow /}} Crow Triggers{{/b}}{{/lg}}
{{sm}}{{i}}Theme: Death, decay, necromancy, control{{/i}}{{/sm}}

{{b}}{{crow /}} Onslaught{{/b}}: Take this Action again against a different target (once per Activation).
{{b}}{{crow /}} Drain Life{{/b}}: After damaging, heal 1/2/3.
{{b}}{{crow /}} Infect{{/b}}: Target gains Poison +2.
{{b}}{{crow /}} Reanimate{{/b}}: After killing the target, Summon a 5-cost Undead model in base contact.
{{b}}{{crow /}} {{crow /}} Dark Fate{{/b}}: Target discards a card or suffers +2 damage.

{{hr /}}

{{lg}}{{b}}{{ram /}} Ram Triggers{{/b}}{{/lg}}
{{sm}}{{i}}Theme: Power, resilience, brute force{{/i}}{{/sm}}

{{b}}{{ram /}} Critical Strike{{/b}}: +1 damage per {{ram /}} in final total.
{{b}}{{ram /}} Knockback{{/b}}: Push target 2" directly away.
{{b}}{{ram /}} Stagger{{/b}}: Target gains Staggered.
{{b}}{{ram /}} Armor Piercing{{/b}}: This Action ignores Armor.
{{b}}{{ram /}} {{ram /}} Crushing Blow{{/b}}: Damage flip receives {{positive /}}.

{{hr /}}

{{lg}}{{b}}{{tome /}} Tome Triggers{{/b}}{{/lg}}
{{sm}}{{i}}Theme: Magic, knowledge, arcane power{{/i}}{{/sm}}

{{b}}{{tome /}} Arcane Enhancement{{/b}}: After resolving, draw a card.
{{b}}{{tome /}} Overpower{{/b}}: Target gains a negative Condition.
{{b}}{{tome /}} Shield of Magic{{/b}}: This model gains Shielded +1.
{{b}}{{tome /}} Burning Touch{{/b}}: Target gains Burning +1.
{{b}}{{tome /}} {{tome /}} Surge{{/b}}: After resolving, take a free Interact Action.

{{hr /}}

{{lg}}{{b}}{{mask /}} Mask Triggers{{/b}}{{/lg}}
{{sm}}{{i}}Theme: Speed, trickery, deception{{/i}}{{/sm}}

{{b}}{{mask /}} Fade Away{{/b}}: Push 3" after resolving.
{{b}}{{mask /}} Backstab{{/b}}: +1 damage if target Engaged with another friendly model.
{{b}}{{mask /}} Diversion{{/b}}: Target can't take {{melee /}} Actions until its next Activation.
{{b}}{{mask /}} Nimble{{/b}}: After resolving, take a free Walk Action.
{{b}}{{mask /}} {{mask /}} Vanish{{/b}}: After resolving, Bury this model.

{{hr /}}

{{lg}}{{b}}Multi-Suit Triggers{{/b}}{{/lg}}
{{sm}}{{i}}Require two different suits — powerful but hard to achieve{{/i}}{{/sm}}

{{b}}{{crow /}} {{mask /}} Misery Loves Company{{/b}}: After damaging, every enemy within {{pulse /}} 3" suffers 1 damage.
{{b}}{{ram /}} {{tome /}} Elemental Strike{{/b}}: +2 damage and target gains Burning +1.
{{b}}{{tome /}} {{mask /}} Ice Mirror{{/b}}: Draw LoS from a friendly Ice Pillar instead of this model.
{{b}}{{crow /}} {{ram /}} Death Grip{{/b}}: Target cannot move or be Pushed until this model's next Activation.
C],
        ], [
            'Initial trigger reference',
            'Added multi-suit triggers, more examples per suit',
            'Added built-in suit rules, thematic descriptions, multi-suit section',
        ]);

        // ── Index 5: "Terrain Types" — 1 version (image type) ─────────

        $this->command->info('  Index: Terrain Types (1 version, image type)');

        $indices['terrain'] = $this->createVersionChain(Index::class, [
            ['title' => 'Terrain Types', 'type' => IndexTypeEnum::Image, 'image' => '/Images/page_banner_top.png', 'content' => ''],
        ], ['Initial terrain types diagram']);

        return $indices;
    }

    // ═════════════════════════════════════════════════════════════
    //  CROSS-REFERENCES
    // ═════════════════════════════════════════════════════════════

    private function addCrossReferences(array $pages, array $sections, array $indices): void
    {
        $this->command->info('  Adding cross-references...');

        // Page → Section references
        $pages['general_actions']->update([
            'content' => $pages['general_actions']->content
                ."\n\n{{b}}Related:{{/b}} See {{sectionLink={$sections['actions']->slug}}}the Actions section{{/sectionLink}} for detailed action types and timing.",
        ]);

        $pages['combat']->update([
            'content' => $pages['combat']->content
                ."\n\n{{b}}See Also:{{/b}} {{sectionLink={$sections['damage']->slug}}}Damage & Healing{{/sectionLink}} for damage resolution details.",
        ]);

        $pages['terrain']->update([
            'content' => $pages['terrain']->content
                ."\n\n{{b}}Reference:{{/b}} {{sectionLink={$sections['auras']->slug}}}Auras & Pulses{{/sectionLink}} for area effects that interact with terrain positioning.",
        ]);

        // Page → Page references
        $pages['rules']->update([
            'content' => $pages['rules']->content
                ."\n\n{{b}}Next:{{/b}} {{pageLink={$pages['encounter']->slug}}}Encounter Setup{{/pageLink}}",
        ]);

        $pages['encounter']->update([
            'content' => $pages['encounter']->content
                ."\n\n{{b}}Next:{{/b}} {{pageLink={$pages['activation']->slug}}}The Activation Phase{{/pageLink}}",
        ]);

        $pages['activation']->update([
            'content' => $pages['activation']->content
                ."\n\n{{b}}Next:{{/b}} {{pageLink={$pages['general_actions']->slug}}}General Actions{{/pageLink}}",
        ]);

        $pages['general_actions']->update([
            'content' => $pages['general_actions']->content
                ."\n\n{{b}}Next:{{/b}} {{pageLink={$pages['combat']->slug}}}Melee & Ranged Combat{{/pageLink}}",
        ]);

        $pages['soulstones']->update([
            'content' => $pages['soulstones']->content
                ."\n\n{{b}}See Also:{{/b}} {{pageLink={$pages['advanced']->slug}}}Advanced Rules{{/pageLink}}",
        ]);

        // Section → Index references
        $sections['actions']->update([
            'left_column' => $sections['actions']->left_column
                ."\n\nSee also: {{indexTooltip={$indices['abilities']->slug}}}Abilities Index{{/indexTooltip}} for ability definitions.",
        ]);

        $sections['duels']->update([
            'right_column' => $sections['duels']->right_column
                ."\n\nFor trigger details, see {{indexTooltip={$indices['triggers']->slug}}}the Trigger Reference{{/indexTooltip}}.",
        ]);

        // Section → Section references
        $sections['duels']->update([
            'left_column' => $sections['duels']->left_column
                ."\n\nSome duels apply {{sectionLink={$sections['conditions']->slug}}}Conditions{{/sectionLink}} to the target.",
        ]);

        $sections['damage']->update([
            'left_column' => $sections['damage']->left_column
                ."\n\nSee {{sectionLink={$sections['duels']->slug}}}Duels{{/sectionLink}} for how attack duels are resolved.",
        ]);

        $sections['auras']->update([
            'left_column' => $sections['auras']->left_column
                ."\n\nFor markers created by some Aura effects, see {{sectionLink={$sections['markers']->slug}}}Markers & Tokens{{/sectionLink}}.",
        ]);

        // Section → Index references (more)
        $sections['signature']->update([
            'left_column' => $sections['signature']->left_column
                ."\n\nFor common melee actions compared to Signature Actions, see {{indexTooltip={$indices['melee']->slug}}}the Melee Actions Index{{/indexTooltip}}.",
        ]);
    }
}
