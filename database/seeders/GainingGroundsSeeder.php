<?php

namespace Database\Seeders;

use App\Enums\SuitEnum;
use App\Models\Scheme;
use App\Models\Season;
use App\Models\SeasonPage;
use App\Models\Strategy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the Gaining Grounds Zero season with all strategies and schemes
 * from the official Malifaux 4th Edition Gaining Grounds Zero document.
 *
 * Usage: php artisan db:seed --class=GainingGroundsSeeder
 */
class GainingGroundsSeeder extends Seeder
{
    private User $admin;

    private Season $season;

    public function run(): void
    {
        $this->admin = User::where('email', 'admin@test.com')->first()
            ?? User::factory()->create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
            ]);

        $this->command->info('Using admin user: admin@test.com');

        // Create Season
        $this->season = Season::create([
            'title' => 'Gaining Grounds Zero',
            'content' => $this->seasonContent(),
            'url' => 'https://www.wyrd-games.net',
        ]);
        $this->approveAndPublish($this->season);
        $this->command->info('Created season: Gaining Grounds Zero');

        // Create Season Pages
        $seasonPages = $this->seasonPageEntries();
        foreach ($seasonPages as $entry) {
            $page = SeasonPage::create(array_merge($entry, [
                'season_id' => $this->season->id,
            ]));
            $this->approveAndPublish($page);
        }
        $this->command->info('Created '.count($seasonPages).' season pages.');

        // Create Strategies
        $strategies = $this->strategyEntries();
        foreach ($strategies as $entry) {
            $strategy = Strategy::create(array_merge($entry, [
                'season_id' => $this->season->id,
            ]));
            $this->approveAndPublish($strategy);
        }
        $this->command->info('Created '.count($strategies).' strategies.');

        // Create Schemes (first pass - without next_scheme links)
        $schemeEntries = $this->schemeEntries();
        $createdSchemes = [];
        foreach ($schemeEntries as $entry) {
            $nextSchemeKeys = [
                'next_scheme_1_key' => $entry['next_scheme_1_key'] ?? null,
                'next_scheme_2_key' => $entry['next_scheme_2_key'] ?? null,
                'next_scheme_3_key' => $entry['next_scheme_3_key'] ?? null,
            ];
            unset($entry['next_scheme_1_key'], $entry['next_scheme_2_key'], $entry['next_scheme_3_key']);

            $scheme = Scheme::create(array_merge($entry, [
                'season_id' => $this->season->id,
            ]));
            $this->approveAndPublish($scheme);
            $createdSchemes[$entry['title']] = [
                'model' => $scheme,
                'links' => $nextSchemeKeys,
            ];
        }
        $this->command->info('Created '.count($createdSchemes).' schemes.');

        // Second pass - link next_scheme references
        $linkCount = 0;
        foreach ($createdSchemes as $data) {
            $updates = [];
            foreach (['next_scheme_1_key' => 'next_scheme_1', 'next_scheme_2_key' => 'next_scheme_2', 'next_scheme_3_key' => 'next_scheme_3'] as $linkKey => $column) {
                if ($data['links'][$linkKey] && isset($createdSchemes[$data['links'][$linkKey]])) {
                    $updates[$column] = $createdSchemes[$data['links'][$linkKey]]['model']->id;
                }
            }
            if (! empty($updates)) {
                $data['model']->updateQuietly($updates);
                $linkCount++;
            }
        }
        $this->command->info("Linked next-scheme references on {$linkCount} schemes.");

        $this->command->newLine();
        $this->command->info('Done! Seeded Gaining Grounds Zero.');
    }

    private function approveAndPublish($model): void
    {
        $model->approval()->create([
            'initiated_by' => $this->admin->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
            'change_notes' => 'Initial seed from Gaining Grounds Zero',
        ]);

        $model->load(['approval', 'previousVersion']);
        $model->publish($this->admin);
    }

    private function seasonContent(): string
    {
        return <<<'HTML'
<h2>Introduction</h2>
<p>Gaining Grounds is the official tournament format for Malifaux. This document will be updated as needed in addition to the beginning of every tournament season and will be made available on our website: wyrd-games.net.</p>
<p>Every Gaining Grounds event is run by a Tournament Organizer, or TO, who is responsible for running the event and acting as a rules judge. This includes organizing the rounds, setting pairings, and determining the winner(s). The TO may choose to make alterations to this document as they see fit to adjust events for their local communities, but any changes should be clearly noted before the event. For some larger events, the TO may see fit to bring in help to act as additional rules judges.</p>

<h2>Overview</h2>
<p>Gaining Grounds events are a competitive environment that pits players against a wide field of opponents to determine an overall winner. While these events are competitive, players of all skill levels are welcome. All players participating in an event will be matched against other players in a series of rounds, with each round earning a player points toward achieving an overall victory for the entire event.</p>
<p>All Gaining Grounds events are fixed faction, so players attending the event must declare one faction that they will play through the entire event.</p>

<h2>Player Responsibilities</h2>
<p>While the TO is responsible for the organization of an event, players have a number of responsibilities when they attend events.</p>
<p>The primary responsibility of the players is to be welcoming and respectful to other players at all times. Wargames are built upon communities, and Gaining Grounds events welcome all members of the community. Players at events are expected to be respectful and practice good sportsmanship. A player who does not meet these expectations can be removed from the event at any time by the TO.</p>
<p>The secondary responsibility of the players is to have all of the materials they need to play the game. This includes all of the models they'll be using, a fate deck, a way to easily measure, any tokens or markers needed for tracking, and all relevant game cards (stat cards, upgrade cards, etc.). Players are also encouraged to bring a copy of the rules for easy reference, though this is not required.</p>
<p>Players are not required to have the most recent errata cards on hand for Gaining Grounds events. However, they must have the official rules for the most recent version of any game cards they use on hand via the Malifaux Crew Builder app or some other means.</p>
<p>The final responsibility of the players is to assist the TO. Remember that the TO is not playing the game, and they have organized this for the benefit of the players. The main ways that players can assist the TO are:</p>
<ul>
<li>Checking in when they arrive at the event, including providing the TO with all necessary information (such as the faction being played).</li>
<li>Listening respectfully when the TO is speaking.</li>
<li>Submitting game results in a clear and timely manner.</li>
<li>Being respectful to opponents and other players.</li>
</ul>

<h3>The Rules of the Game</h3>
<p>Players are expected to be familiar with the game's rules. All Gaining Grounds games are played using the Malifaux: Fourth Edition rules, including any FAQ and errata released at least one month prior to the event. If any rules disputes arise and cannot be settled among the players, the TO and any designated rules judges are the sole authority on the rules at Gaining Grounds events, and they are expected to be fair and equitable in their decisions. Their decision(s) at an event is final.</p>

<h3>Deck Etiquette</h3>
<p>Players may not touch a fate deck unless the rules specifically call for them to do so (such as when drawing or shuffling) or if the deck needs to be moved because it is in the way. Picking up or fiddling with a deck when it is unnecessary is not allowed.</p>

<h3>Public Information</h3>
<p>All information in Gaining Grounds games is considered public information unless it is specifically stated otherwise in the rules (such as a player's control hand, fate deck, chosen schemes).</p>
<p>If a player's opponent wants to see a game card, know how many fate cards are in their control hand, or seeks other pertinent information, that information must be provided. Players may not lie or purposefully mislead their opponents about public information in the game. Information about the outcomes of games and the status of the event is also public information.</p>

<h3>Slow Play</h3>
<p>Since time is limited in an event, it is important that players make decisions quickly to move the game along. If a player is taking too long to play, it may be considered poor sportsmanship. Players should make every effort to complete the entire game in the time allotted.</p>
<p>If you feel your opponent is playing too slowly, call the Organizer over to have them make a decision or take other corrective measures. Slow play may be unintentional, but it should be avoided when possible.</p>

<h3>Tracking</h3>
<p>Every attempt should be made to keep the table clean so as to avoid confusion in the eventuality a TO needs to make a ruling. Players must track activations, health, and tokens with any combination of the following methods, which must be announced before the game:</p>
<ul>
<li>The official Crew Builder app from Wyrd Games.</li>
<li>An erasable marker used to write on a model's stat card.</li>
<li>Dice of differing colors for each effect.</li>
<li>Markers or dials of differing colors or symbols for each effect.</li>
</ul>

<h2>Painting</h2>
<p>Gaining Grounds events do not require any players to use painted models, but players are greatly encouraged to do so. The hobby aspect of a skirmish game is very rewarding, and a painted crew on the table looks much more intimidating to opponents!</p>

<h3>Proxies and Conversions</h3>
<p>Players are expected to use official models when playing Malifaux as this facilitates the ease of understanding for that player's opponents. Proxy models are not allowed, though official "counts as" models may be used. The only exception to this rule is if a model has an officially released stat card available but no model is yet released, the player may field a suitable proxy but it must be easily identifiable, as per the TO's discretion. With that said, conversions are allowed at Gaining Grounds events with the following restrictions:</p>
<ul>
<li>The converted model must still clearly represent the model it is converted from.</li>
<li>No more than 50% of the finished model may be built using non-Wyrd components.</li>
</ul>

<h2>Table Setup</h2>

<h3>Terrain Density</h3>
<p>When putting together a Malifaux table it is best to cover the board in roughly 45%-65% terrain of various shapes and types. This can easily be measured by setting all the terrain for a table to one side until they roughly cover about 45% of the table when placed next to each other. Once you have enough terrain it can be spread out across the board to create paths, chokepoints, etc.</p>

<h2>Tournament Structure</h2>

<h3>Game Rounds</h3>
<p>Gaining Grounds events are played over a number of rounds, each consisting of a single game of Malifaux.</p>

<h4>Number of Rounds</h4>
<ul>
<li>4-15 attendees: 3 round event</li>
<li>16-32 attendees: 4 round event</li>
<li>33+ attendees: 5+ round event</li>
</ul>

<h4>Round Time Limit</h4>
<p>The amount of time for each round is based on the size of the game. For a 50 soulstone game, which is the standard format, it is recommended that players have 120-135 minutes to play and an additional 15 minutes for encounter setup.</p>
<p>When there are 15 minutes left in a round, the TO will call last turn. Players should complete the turn they are on and end the game in step F of the end phase regardless of the current turn.</p>
<p>When the TO calls time, players should complete the activation they are on. No new activation may be started after this call is made, and when the activation is complete players immediately proceed to the end phase of the turn, ending the game in step F regardless of the current turn.</p>

<h3>Scoring</h3>
<p>At the end of each round, players record the results of the game, based on the encounter used in the game and the formula below.</p>
<p>A player earns a number of tournament points (TP) based on the result of the game. Players earn 3 TP for a win, 1 for a tie, and 0 for a loss. The player records the total number of VP they scored in the game.</p>
<p>A player also gains a differential (DIFF) equal to the difference in the number of victory points (VP) between themselves and their opponent.</p>

<h3>Event Standings</h3>
<p>Players are ranked from highest to lowest as follows:</p>
<ol>
<li>Players are ranked by their total TP, so players with higher TP finish above those with lower TP.</li>
<li>Players who have the same TP as each other are then ranked by their total differential (DIFF).</li>
<li>Finally, players who are still tied are ranked by their total victory points (VP).</li>
</ol>

<h2>Gameplay Changes</h2>

<h3>Set-Up Changes</h3>
<p>Gaining Grounds events are played using the encounter setup from the core rulebook, except as follows:</p>
<p><strong>Determine Encounter Size:</strong> The standard game size of a Gaining Grounds event is 50 soulstones.</p>
<p><strong>Choose Faction and Leader:</strong> All Gaining Grounds events use fixed factions. As such, players may only choose a leader that is within the faction they declared for the event.</p>

<h3>Secretly Chosen Info</h3>
<p>When any effect has a player secretly choose information (such as a model, terrain, etc), all relevant information must be noted by that player so that it may be referenced at a later time.</p>

<h2>Optional Game Variants</h2>
<p>When preparing and running an event, some TOs may find that their community enjoys a specific aspect of the game over others. TOs are encouraged to utilize the game variant(s) for their event that are appropriate for their community.</p>

<h3>Adjusted Rules</h3>
<h4>Unreachable Areas</h4>
<p>When playing in a Gaining Grounds event, there may be some locations on the game board that are unreachable through normal means of play, such as atop or within a lofty mountain, sectioned off from the board via an inexplicably tall fence, or within the center of impassable terrain, and those areas that a Sp 6 model without any additional movement capabilities (such as Flight) cannot reach in a single activation.</p>
<p>Unreachable areas can never have markers or models placed inside or moved within them, and should never take up more than 20% of a player's deployment zone.</p>
HTML;
    }

    /**
     * @return array<int, array{title: string, sort_order: int, content: string}>
     */
    private function seasonPageEntries(): array
    {
        return [
            [
                'title' => 'Introduction',
                'sort_order' => 0,
                'content' => <<<'HTML'
<h2>Introduction</h2>
<p>Gaining Grounds is the official tournament format for Malifaux. This document will be updated as needed in addition to the beginning of every tournament season and will be made available on our website: wyrd-games.net.</p>
<p>Every Gaining Grounds event is run by a Tournament Organizer, or TO, who is responsible for running the event and acting as a rules judge. This includes organizing the rounds, setting pairings, and determining the winner(s). The TO may choose to make alterations to this document as they see fit to adjust events for their local communities, but any changes should be clearly noted before the event. For some larger events, the TO may see fit to bring in help to act as additional rules judges.</p>

<h2>Overview</h2>
<p>Gaining Grounds events are a competitive environment that pits players against a wide field of opponents to determine an overall winner. While these events are competitive, players of all skill levels are welcome. All players participating in an event will be matched against other players in a series of rounds, with each round earning a player points toward achieving an overall victory for the entire event.</p>
<p>All Gaining Grounds events are fixed faction, so players attending the event must declare one faction that they will play through the entire event.</p>
HTML,
            ],
            [
                'title' => 'Player Responsibilities',
                'sort_order' => 1,
                'content' => <<<'HTML'
<h2>Player Responsibilities</h2>
<p>While the TO is responsible for the organization of an event, players have a number of responsibilities when they attend events.</p>
<p>The primary responsibility of the players is to be welcoming and respectful to other players at all times. Wargames are built upon communities, and Gaining Grounds events welcome all members of the community. Players at events are expected to be respectful and practice good sportsmanship. A player who does not meet these expectations can be removed from the event at any time by the TO.</p>
<p>The secondary responsibility of the players is to have all of the materials they need to play the game. This includes all of the models they'll be using, a fate deck, a way to easily measure, any tokens or markers needed for tracking, and all relevant game cards (stat cards, upgrade cards, etc.). Players are also encouraged to bring a copy of the rules for easy reference, though this is not required.</p>
<p>Players are not required to have the most recent errata cards on hand for Gaining Grounds events. However, they must have the official rules for the most recent version of any game cards they use on hand via the Malifaux Crew Builder app or some other means.</p>
<p>The final responsibility of the players is to assist the TO. Remember that the TO is not playing the game, and they have organized this for the benefit of the players. The main ways that players can assist the TO are:</p>
<ul>
<li>Checking in when they arrive at the event, including providing the TO with all necessary information (such as the faction being played).</li>
<li>Listening respectfully when the TO is speaking.</li>
<li>Submitting game results in a clear and timely manner.</li>
<li>Being respectful to opponents and other players.</li>
</ul>

<h3>The Rules of the Game</h3>
<p>Players are expected to be familiar with the game's rules. All Gaining Grounds games are played using the Malifaux: Fourth Edition rules, including any FAQ and errata released at least one month prior to the event. If any rules disputes arise and cannot be settled among the players, the TO and any designated rules judges are the sole authority on the rules at Gaining Grounds events, and they are expected to be fair and equitable in their decisions. Their decision(s) at an event is final.</p>

<h3>Deck Etiquette</h3>
<p>Players may not touch a fate deck unless the rules specifically call for them to do so (such as when drawing or shuffling) or if the deck needs to be moved because it is in the way. Picking up or fiddling with a deck when it is unnecessary is not allowed.</p>

<h3>Public Information</h3>
<p>All information in Gaining Grounds games is considered public information unless it is specifically stated otherwise in the rules (such as a player's control hand, fate deck, chosen schemes).</p>
<p>If a player's opponent wants to see a game card, know how many fate cards are in their control hand, or seeks other pertinent information, that information must be provided. Players may not lie or purposefully mislead their opponents about public information in the game. Information about the outcomes of games and the status of the event is also public information.</p>

<h3>Slow Play</h3>
<p>Since time is limited in an event, it is important that players make decisions quickly to move the game along. If a player is taking too long to play, it may be considered poor sportsmanship. Players should make every effort to complete the entire game in the time allotted.</p>
<p>If you feel your opponent is playing too slowly, call the Organizer over to have them make a decision or take other corrective measures. Slow play may be unintentional, but it should be avoided when possible.</p>

<h3>Tracking</h3>
<p>Every attempt should be made to keep the table clean so as to avoid confusion in the eventuality a TO needs to make a ruling. Players must track activations, health, and tokens with any combination of the following methods, which must be announced before the game:</p>
<ul>
<li>The official Crew Builder app from Wyrd Games.</li>
<li>An erasable marker used to write on a model's stat card.</li>
<li>Dice of differing colors for each effect.</li>
<li>Markers or dials of differing colors or symbols for each effect.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Painting & Table Setup',
                'sort_order' => 2,
                'content' => <<<'HTML'
<h2>Painting</h2>
<p>Gaining Grounds events do not require any players to use painted models, but players are greatly encouraged to do so. The hobby aspect of a skirmish game is very rewarding, and a painted crew on the table looks much more intimidating to opponents!</p>

<h3>Proxies and Conversions</h3>
<p>Players are expected to use official models when playing Malifaux as this facilitates the ease of understanding for that player's opponents. Proxy models are not allowed, though official "counts as" models may be used. The only exception to this rule is if a model has an officially released stat card available but no model is yet released, the player may field a suitable proxy but it must be easily identifiable, as per the TO's discretion. With that said, conversions are allowed at Gaining Grounds events with the following restrictions:</p>
<ul>
<li>The converted model must still clearly represent the model it is converted from.</li>
<li>No more than 50% of the finished model may be built using non-Wyrd components.</li>
</ul>

<h2>Table Setup</h2>

<h3>Terrain Density</h3>
<p>When putting together a Malifaux table it is best to cover the board in roughly 45%-65% terrain of various shapes and types. This can easily be measured by setting all the terrain for a table to one side until they roughly cover about 45% of the table when placed next to each other. Once you have enough terrain it can be spread out across the board to create paths, chokepoints, etc.</p>
HTML,
            ],
            [
                'title' => 'Tournament Structure',
                'sort_order' => 3,
                'content' => <<<'HTML'
<h2>Tournament Structure</h2>

<h3>Game Rounds</h3>
<p>Gaining Grounds events are played over a number of rounds, each consisting of a single game of Malifaux.</p>

<h4>Number of Rounds</h4>
<ul>
<li>4-15 attendees: 3 round event</li>
<li>16-32 attendees: 4 round event</li>
<li>33+ attendees: 5+ round event</li>
</ul>

<h4>Round Time Limit</h4>
<p>The amount of time for each round is based on the size of the game. For a 50 soulstone game, which is the standard format, it is recommended that players have 120-135 minutes to play and an additional 15 minutes for encounter setup.</p>
<p>When there are 15 minutes left in a round, the TO will call last turn. Players should complete the turn they are on and end the game in step F of the end phase regardless of the current turn.</p>
<p>When the TO calls time, players should complete the activation they are on. No new activation may be started after this call is made, and when the activation is complete players immediately proceed to the end phase of the turn, ending the game in step F regardless of the current turn.</p>

<h3>Scoring</h3>
<p>At the end of each round, players record the results of the game, based on the encounter used in the game and the formula below.</p>
<p>A player earns a number of tournament points (TP) based on the result of the game. Players earn 3 TP for a win, 1 for a tie, and 0 for a loss. The player records the total number of VP they scored in the game.</p>
<p>A player also gains a differential (DIFF) equal to the difference in the number of victory points (VP) between themselves and their opponent.</p>

<h3>Event Standings</h3>
<p>Players are ranked from highest to lowest as follows:</p>
<ol>
<li>Players are ranked by their total TP, so players with higher TP finish above those with lower TP.</li>
<li>Players who have the same TP as each other are then ranked by their total differential (DIFF).</li>
<li>Finally, players who are still tied are ranked by their total victory points (VP).</li>
</ol>
HTML,
            ],
            [
                'title' => 'Gameplay Changes',
                'sort_order' => 4,
                'content' => <<<'HTML'
<h2>Gameplay Changes</h2>

<h3>Set-Up Changes</h3>
<p>Gaining Grounds events are played using the encounter setup from the core rulebook, except as follows:</p>
<p><strong>Determine Encounter Size:</strong> The standard game size of a Gaining Grounds event is 50 soulstones.</p>
<p><strong>Choose Faction and Leader:</strong> All Gaining Grounds events use fixed factions. As such, players may only choose a leader that is within the faction they declared for the event.</p>

<h3>Secretly Chosen Info</h3>
<p>When any effect has a player secretly choose information (such as a model, terrain, etc), all relevant information must be noted by that player so that it may be referenced at a later time.</p>

<h2>Optional Game Variants</h2>
<p>When preparing and running an event, some TOs may find that their community enjoys a specific aspect of the game over others. TOs are encouraged to utilize the game variant(s) for their event that are appropriate for their community.</p>

<h3>Adjusted Rules</h3>
<h4>Unreachable Areas</h4>
<p>When playing in a Gaining Grounds event, there may be some locations on the game board that are unreachable through normal means of play, such as atop or within a lofty mountain, sectioned off from the board via an inexplicably tall fence, or within the center of impassable terrain, and those areas that a Sp 6 model without any additional movement capabilities (such as Flight) cannot reach in a single activation.</p>
<p>Unreachable areas can never have markers or models placed inside or moved within them, and should never take up more than 20% of a player's deployment zone.</p>
HTML,
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, suit: SuitEnum, setup: string|null, rules: string|null, scoring: string|null, additional: string|null}>
     */
    private function strategyEntries(): array
    {
        return [
            [
                'title' => 'Plant Explosives',
                'suit' => SuitEnum::Tomes,
                'setup' => '<p>After deployment, each non-peon model in play gains an Explosive token.</p>',
                'rules' => '<p>A model with an Explosive token may take the Interact action to remove the token and make a friendly Strategy marker within 1", and not within 4" of another friendly Strategy marker. Explosive tokens may not be removed in any other way.</p><p>A model may target a Strategy marker with the Interact action to remove it; if the model does not have an Explosive token, it gains one.</p><p>After a model with an Explosive token is killed, the model that killed it makes a neutral Strategy marker within 1" of the killed model, if able. Models may move on top of Strategy markers.</p>',
                'scoring' => '<p>At the end of every turn, each crew counts how many friendly Strategy markers they have completely on the enemy table half. The crew with the highest total gains 1 VP. In the case of a tie, both crews gain 1 VP.</p>',
                'additional' => '<p>Once per crew per game, at the end of the turn this crew gains 1 VP if there are two or more friendly Strategy markers in the enemy deployment zone.</p>',
            ],
            [
                'title' => 'Boundary Dispute',
                'suit' => SuitEnum::Masks,
                'setup' => '<p>After deployment zones are chosen, starting with the attacker, each player alternates making three Strategy markers completely in their deployment zone, not within 6" of another Strategy marker.</p>',
                'rules' => '<p>Strategy markers are friendly to the player that made them. A model may target a Strategy marker with the Interact action to place it within 6" of its current location, not in base contact with any model(s).</p>',
                'scoring' => '<p>At the end of every turn, the crew with the most friendly Strategy markers completely on the enemy table half gains 1 VP. Strategy markers completely in the enemy deployment zone are counted twice. In the case of a tie, both crews gain 1 VP.</p><p>Then the crew that has scored the least total VP from this strategy this game may select any one friendly Strategy marker and place it within 4" of its current location, not in base contact with any model(s).</p>',
                'additional' => '<p>Double any victory points gained from this strategy on turn 4.</p>',
            ],
            [
                'title' => 'Recover Evidence',
                'suit' => SuitEnum::Crows,
                'setup' => '<p>After deployment zones are chosen, starting with the attacker, each player makes one Strategy marker completely on the enemy table half.</p>',
                'rules' => '<p>After a model is killed by the enemy crew, the enemy makes a Strategy marker within 3" of the killed model.</p><p>A model may target a friendly Strategy marker with the Interact action to remove the marker and put it onto that crew\'s crew card. Models may move on top of Strategy markers.</p>',
                'scoring' => '<p>At the end of every turn, the crew with the most Strategy markers on its crew card gains 1 VP. In the case of a tie, both crews gain 1 VP. All crews then remove all Strategy markers from their crew cards.</p>',
                'additional' => '<p>Once per crew per game. At the end of any friendly activation, this crew may select a piece of terrain within 6" of the enemy deployment zone and remove a number of friendly Scheme markers equal to the turn number from within 1" of it to gain 1 VP.</p>',
            ],
            [
                'title' => 'Informants',
                'suit' => SuitEnum::Rams,
                'setup' => '<p>After deployment zones are chosen, make five Strategy markers:</p><ul><li>One centered on the centerpoint.</li><li>Two centered on the centerline, each 10" to the left and right of the centerpoint, respectively.</li><li>Starting with the attacker, each player alternates making one Strategy marker in the center of one table quarter completely on their side of the board.</li></ul>',
                'rules' => '<p>A crew controls a Strategy marker if it has more models without Summon tokens within 2" of the marker than any opponent does.</p>',
                'scoring' => '<p>At the end of every turn, the crew controlling the most Strategy markers gains 1 VP. In the case of a tie, both crews gain 1 VP.</p><p>Then the crew that has scored the least total VP from this strategy this game selects up to two Strategy markers and places them within 3" of their location, not in base contact with any model(s) or within 8" of any other Strategy marker(s).</p>',
                'additional' => '<p>Double any victory points gained from this strategy on turn 4.</p>',
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, prerequisites: string|null, reveal: string|null, scoring: string|null, additional: string|null, next_scheme_1_key: string|null, next_scheme_2_key: string|null, next_scheme_3_key: string|null}>
     */
    private function schemeEntries(): array
    {
        return [
            [
                'title' => 'Breakthrough',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme when an enemy model ends its activation.</p>',
                'scoring' => '<p>When this scheme is revealed, remove one friendly Scheme marker in the enemy deployment zone that does not have an enemy model within 2" of it to gain 1 VP.</p>',
                'additional' => '<p>When this scheme is revealed you may also remove one friendly Scheme marker from the centerline and one friendly Scheme marker from your deployment zone to gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Assassinate',
                'next_scheme_2_key' => 'Public Demonstration',
                'next_scheme_3_key' => 'Frame Job',
            ],
            [
                'title' => 'Frame Job',
                'prerequisites' => '<p>When this scheme is selected, secretly choose a friendly model.</p>',
                'reveal' => '<p>You may reveal this scheme after the chosen model suffers damage from an enemy attack action targeting it while it is on the enemy table half.</p>',
                'scoring' => null,
                'additional' => '<p>When this scheme is revealed, you may remove one friendly Scheme marker from within 2" of the chosen model to gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Public Demonstration',
                'next_scheme_2_key' => 'Harness the Leyline',
                'next_scheme_3_key' => 'Scout the Rooftops',
            ],
            [
                'title' => 'Assassinate',
                'prerequisites' => '<p>When this scheme is selected, secretly choose a unique enemy model that has half or more of its maximum health remaining. You may want to ask about the health of multiple models (even if you do not select this scheme) to fool your opponent.</p>',
                'reveal' => '<p>You may reveal this scheme after the chosen model is reduced to below half of its maximum health.</p>',
                'scoring' => null,
                'additional' => '<p>At the end of the turn on which this scheme was revealed, if the chosen model has been killed, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Scout the Rooftops',
                'next_scheme_2_key' => 'Detonate Charges',
                'next_scheme_3_key' => 'Runic Binding',
            ],
            [
                'title' => 'Scout the Rooftops',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>Scheme markers are qualifying for this scheme if they:</p><ul><li>Are not within 6" of your deployment zone.</li><li>Do not have an enemy model at the same elevation within 2".</li><li>Are at elevation 2 or higher.</li></ul><p>When this scheme is revealed, remove one qualifying Scheme marker from two different terrain pieces to gain 1 VP (two Scheme markers total).</p>',
                'additional' => '<p>When this scheme is revealed, select one additional qualifying Scheme marker that is completely on the enemy table half and remove it to gain 1 VP. Note that this Scheme marker may be on the same terrain piece as one of the other qualifying Scheme markers.</p>',
                'next_scheme_1_key' => 'Detonate Charges',
                'next_scheme_2_key' => 'Grave Robbing',
                'next_scheme_3_key' => 'Leave Your Mark',
            ],
            [
                'title' => 'Detonate Charges',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>When this scheme is revealed, remove two friendly Scheme markers that are within 2" of enemy model(s) to gain 1 VP.</p>',
                'additional' => '<p>When this scheme is revealed you may remove one additional qualifying marker to gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Grave Robbing',
                'next_scheme_2_key' => 'Runic Binding',
                'next_scheme_3_key' => 'Take the Highground',
            ],
            [
                'title' => 'Ensnare',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme when an enemy model ends its activation.</p>',
                'scoring' => '<p>When this scheme is revealed, remove two friendly Scheme markers from within 2" of a single unique enemy model to gain 1 VP.</p>',
                'additional' => '<p>When this scheme is revealed, if the enemy unique model is engaged by a model of lower cost, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Reshape the Land',
                'next_scheme_2_key' => 'Search the Area',
                'next_scheme_3_key' => 'Frame Job',
            ],
            [
                'title' => 'Make it Look Like an Accident',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme when an enemy model suffers damage due to falling.</p>',
                'scoring' => null,
                'additional' => '<p>If at the end of the turn on which this scheme was revealed the enemy model that fell has been killed or has less than half of its maximum health, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Ensnare',
                'next_scheme_2_key' => 'Reshape the Land',
                'next_scheme_3_key' => 'Breakthrough',
            ],
            [
                'title' => 'Harness the Leyline',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>When this scheme is revealed, remove two friendly Scheme markers on the centerline not within 6" of another marker used to score this scheme and that do not have any enemy models within 2" to gain 1 VP.</p>',
                'additional' => '<p>Remove one additional qualifying marker to gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Assassinate',
                'next_scheme_2_key' => 'Scout the Rooftops',
                'next_scheme_3_key' => 'Grave Robbing',
            ],
            [
                'title' => 'Search the Area',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme at the end of any enemy activation.</p>',
                'scoring' => '<p>When this scheme is revealed, select a piece of terrain completely on the enemy table half. Remove three friendly Scheme markers from within 1" of the selected terrain that do not have enemy models within 2" of them to gain 1 VP.</p>',
                'additional' => '<p>At the end of the turn on which this scheme was revealed, you may remove one friendly Scheme marker from within 1" of the selected terrain to gain 1 VP.</p>',
                'next_scheme_1_key' => 'Breakthrough',
                'next_scheme_2_key' => 'Frame Job',
                'next_scheme_3_key' => 'Harness the Leyline',
            ],
            [
                'title' => 'Take the Highground',
                'prerequisites' => '<p>A crew is considered to control a terrain piece if it has the most friendly models standing on it. This crew\'s models that are within 6" of their deployment zone are ignored.</p>',
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>When you reveal this scheme, if you control at least two Ht 2 or greater terrain pieces gain 1 VP.</p>',
                'additional' => '<p>If you control at least three qualifying terrain pieces gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Make it Look Like an Accident',
                'next_scheme_2_key' => 'Ensnare',
                'next_scheme_3_key' => 'Search the Area',
            ],
            [
                'title' => 'Grave Robbing',
                'prerequisites' => '<p>When this Scheme is selected, secretly choose a type of non-Scheme marker.</p>',
                'reveal' => '<p>After killing an enemy model within 2" of both one or more friendly Scheme marker(s) and one or more of the chosen marker, reveal this scheme.</p>',
                'scoring' => '<p>When this scheme is revealed, remove one friendly Scheme marker within 2" of the killed model to gain 1 VP.</p>',
                'additional' => '<p>Until the end of the turn, friendly models may target enemy Remains markers with the Interact action to remove them and place them on your crew card. At the end of the turn remove all Remains markers from your crew card that were placed this way. If two or more are removed, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Runic Binding',
                'next_scheme_2_key' => 'Leave Your Mark',
                'next_scheme_3_key' => 'Make it Look Like an Accident',
            ],
            [
                'title' => 'Runic Binding',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme when an enemy model ends its activation.</p>',
                'scoring' => '<p>When this scheme is revealed, choose three friendly Scheme markers in play. Each chosen marker must be within 14" of at least one of the other chosen markers. If there is at least one enemy model within the area formed between the chosen markers, gain 1 VP. Remove the chosen markers.</p>',
                'additional' => '<p>When this scheme is revealed, if the combined cost of the enemy models in that area is 15 or greater, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Leave Your Mark',
                'next_scheme_2_key' => 'Take the Highground',
                'next_scheme_3_key' => 'Ensnare',
            ],
            [
                'title' => 'Reshape the Land',
                'prerequisites' => '<p>When this scheme is selected, secretly choose a marker type.</p>',
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>If there are four friendly markers of the chosen type completely on the enemy table half, gain 1 VP. Then, if the chosen marker type was Scheme, remove all markers used to score this Scheme.</p>',
                'additional' => '<p>If there are five friendly markers of the chosen type completely on the enemy table half, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Search the Area',
                'next_scheme_2_key' => 'Breakthrough',
                'next_scheme_3_key' => 'Public Demonstration',
            ],
            [
                'title' => 'Public Demonstration',
                'prerequisites' => '<p>When this scheme is selected, secretly choose a unique enemy model.</p>',
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>When this scheme is revealed, if there are two or more friendly minions within 2" of the chosen model, gain 1 VP.</p>',
                'additional' => '<p>When this scheme is revealed, remove a friendly Scheme marker from within 1" of the chosen model to gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Harness the Leyline',
                'next_scheme_2_key' => 'Assassinate',
                'next_scheme_3_key' => 'Detonate Charges',
            ],
            [
                'title' => 'Leave Your Mark',
                'prerequisites' => null,
                'reveal' => '<p>You may reveal this scheme at the end of any turn.</p>',
                'scoring' => '<p>When this scheme is revealed, if there are more friendly Scheme markers within 1" of the centerpoint than enemy Scheme markers within 1" of the centerpoint, gain 1 VP. Then, remove all friendly Scheme markers within 1" of the centerpoint.</p>',
                'additional' => '<p>When this scheme is revealed, if there are at least two more friendly Scheme markers within 1" of the centerpoint than enemy Scheme markers within 1" of the centerpoint, gain 1 additional VP.</p>',
                'next_scheme_1_key' => 'Take the Highground',
                'next_scheme_2_key' => 'Make it Look Like an Accident',
                'next_scheme_3_key' => 'Reshape the Land',
            ],
        ];
    }
}
