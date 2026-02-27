<?php

namespace Database\Seeders;

use App\Enums\FaqCategoryEnum;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the faqs table with all Q&A entries from the official
 * Malifaux 4th Edition FAQ (October 2025).
 *
 * Usage: php artisan db:seed --class=FaqSeeder
 */
class FaqSeeder extends Seeder
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

        $entries = $this->faqEntries();
        $count = 0;

        foreach ($entries as $entry) {
            $faq = Faq::create([
                'title' => $entry['title'],
                'category' => $entry['category'],
                'answer' => $entry['answer'],
                'sort_order' => $entry['sort_order'],
            ]);

            $this->approveAndPublish($faq);
            $count++;
        }

        $this->command->newLine();
        $this->command->info("Done! Seeded {$count} FAQ entries.");
    }

    private function approveAndPublish(Faq $faq): void
    {
        $faq->approval()->create([
            'initiated_by' => $this->admin->id,
            'approved_at' => now(),
            'approved_by' => $this->admin->id,
            'change_notes' => 'Initial seed from M4E FAQ (October 2025)',
        ]);

        $faq->load(['approval', 'previousVersion']);
        $faq->publish($this->admin);
    }

    /**
     * @return array<int, array{title: string, category: FaqCategoryEnum, answer: string, sort_order: int}>
     */
    private function faqEntries(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════
            //  SECTION 1: GENERAL (1.1–1.15)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '1.1 – When a model draws LoS from another object, is cover determined from the model, or from the object the model is drawing LoS from?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>If a model is drawing LoS from another object, all sight lines are drawn from that second object. Cover would therefore be determined from the second object\'s position and not the model.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => '1.2 – How does cover interact with non-targeted effects? Can a model caught in a pulse or chosen for the effect of the Breath of Fire action gain cover?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>No. Cover can only be gained when a model is targeted by an action. Neither pulses, nor effects like <em>Breath of Fire</em>, which just deals damage to a nearby model provide cover. This may result in a situation where the target of <em>Breath of Fire</em> suffers less damage than the second model.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => '1.3 – If an action ignores or does not require LoS, are sight lines still drawn to determine cover and concealment?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes. If the action has a target, sight lines are drawn during step A.3 and A.4 of Resolving Actions. Step A.4 is not automatically skipped if step A.3 is skipped.</p>',
                'sort_order' => 3,
            ],
            [
                'title' => '1.4 – What does it mean when a model "ignores [v] abilities," such as the Ruthless ability?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>This means the model with <em>Ruthless</em> treats all [v] abilities on other models as if they didn\'t exist. For example, the <em>Ruthless</em> model\'s damage cannot be reduced by the <em>Threatening Demeanor</em> ability, nor do they suffer a [-] when targeting a model with the <em>Terrifying</em> ability, nor can a model with the <em>Tangled in the Briars</em> ability drain a [s] to place the <em>Ruthless</em> model and give it an Exposed token.</p>',
                'sort_order' => 4,
            ],
            [
                'title' => '1.5 – When a model attempts to move through a friendly model, who chooses if it may?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>No one, there is no choice being made. Friendly models never block friendly models from moving through them.</p>',
                'sort_order' => 5,
            ],
            [
                'title' => '1.6 – What does it mean when an effect "ignores once per" restrictions, such as Harold Tull, Artillerist\'s trigger Gertrude and Ethel?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>If an effect ignores a once per restriction, it both may be used even if the effect has already been used, and does not count against that restriction for future uses. In the case of <em>Gertrude and Ethel</em>, this trigger can either be used after the Walking Cannon has activated for a second <em>Howitzer</em> attack or before the Walking Cannon activates for the first attack, while still allowing a second attack during the Walking Cannon\'s activation (or from some other source, such as <em>Hold Down</em>).</p>',
                'sort_order' => 6,
            ],
            [
                'title' => '1.7 – If I apply a friendly Staggered token to my own model, does that prevent enemy models from moving my model?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 7,
            ],
            [
                'title' => '1.8 – If a model has an enemy Staggered token, can it be moved by its own actions that are controlled by another model?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes. Actions such as <em>Obey</em> that allow a model to declare an action aren\'t directly moving it. Instead, it is moving itself, just not under its own control.</p>',
                'sort_order' => 8,
            ],
            [
                'title' => '1.9 – If my model declares the Walk action while not engaged, then ends the Walk action engaged with an enemy, but also immediately scales a building in such a way that it is no longer engaged, does this count as "leaving engagement with a Walk action?" Can my model still declare the Interact action?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes, you can still declare the Interact action. As per the Engagement section of the rules, a model is prevented from declaring the Interact action if it declared the Walk action while engaged. In the above example, the model declared the Walk while not engaged. The fact that it then entered and left engagement does not matter.</p>',
                'sort_order' => 9,
            ],
            [
                'title' => '1.10 – If a model declares the Walk action while engaged and then scales terrain, leaving the engagement in the process, can they Interact?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>No. Scaling is considered part of the Walk action due to being an after resolving effect and being part of the action still. As such the Walk action was declared while engaged and has been used to leave engagement and the Interact action cannot be declared.</p>',
                'sort_order' => 10,
            ],
            [
                'title' => '1.11 – When a pulse or other area effect affects multiple models at the same time, who chooses the order that models resolve the generated duel?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>The pulse generates one new effect on every model caught inside of it. The controller of the models caught inside the pulse (or other area effect) would choose which to resolve first, one at a time. See pg 40 of the Comprehensive Rule Book (Damage Timing) for an example.</p>',
                'sort_order' => 11,
            ],
            [
                'title' => '1.12 – Many effects in the game refer to multiple objects at a time such as "friendly Scheme or Remains markers." Does the "friendly" restriction apply to both types of objects, or just the one immediately following the word friendly?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>In examples like this, friendly always applies to both objects. This effect should be read, "friendly Scheme or friendly Remains markers." If we want one of the two objects to be alignment agnostic, we will reverse the order they are listed, such as "Remains markers or friendly Scheme markers."</p>',
                'sort_order' => 12,
            ],
            [
                'title' => '1.13 – If an effect adds +1, a suit, a fate modifier, etc., to a model\'s Skl, does that effect last until the end of the game?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>No, just until the action finishes resolving. "Increase this action\'s Skl by +1" and "Increase this action\'s duel total by +1" are identical in practice.</p>',
                'sort_order' => 13,
            ],
            [
                'title' => '1.14 – Are two abilities that are prefaced by the same text (before parenthesis) the same ability?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes. Abilities such as <em>Demise (Eternal)</em> and <em>Demise (Explosive)</em> are considered the same ability. If a model gains a second instance of an ability with the same name as an ability it already has, the second instance becomes active and the first is ignored. Therefore, a model with <em>Demise (Eternal)</em> that gains <em>Demise (Explosive)</em> would ignore <em>Demise (Eternal)</em> and only be able to use <em>Demise (Explosive)</em>. There are some effects that specify Demise abilities, which affect all abilities prefaced by Demise.</p>',
                'sort_order' => 14,
            ],
            [
                'title' => '1.15 – If an effect would allow a model to look at or reveal the top card(s) of a fate deck, and there are not enough cards left in the fate deck, is the fate deck shuffled first?',
                'category' => FaqCategoryEnum::General,
                'answer' => '<p>Yes. For example, if a model declares the <em>Intuition</em> action and there is only one card left in the deck, it first looks at that last remaining card, then reshuffles the deck, then looks at the top two cards of the deck, rearranges all three as it pleases, before placing all three on top of the fresh deck.</p>',
                'sort_order' => 15,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 2: ACTIONS (2.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '2.1 – Can a model that "ignores other models while moving" declare the Interact action after leaving an enemy model\'s engagement range with the Walk action?',
                'category' => FaqCategoryEnum::Actions,
                'answer' => '<p>No. Ignoring other models only allows a model to move through other models, it does not ignore other aspects of those models, such as engagement range.</p>',
                'sort_order' => 1,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 3: TERRAIN (3.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '3.1 – What does "ignoring terrain" mean?',
                'category' => FaqCategoryEnum::Terrain,
                'answer' => '<p>A model that ignores terrain (such as from the <em>Wrecking Ball</em> action) is unaffected (see the Unaffected by Terrain section) by all of that terrain\'s traits, including height, and can thus change elevation during a move that ignores height as if all terrain was flat.</p>',
                'sort_order' => 1,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 4: ENCOUNTERS (4.1–4.8)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '4.1 – Can a Strategy marker be chosen or used by a player for Schemes that can choose/use any non-specified marker?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes. While Strategy markers ignore the effects of models, Schemes such as <em>Grave Robbing</em> or <em>Reshape the Land</em> are the effects of a player and not a model.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => '4.2 – Can a model use the Walk the Line action to move a model toward a Strategy marker?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>No. The <em>Walk the Line</em> action is an action belonging to a model. The action ignores the Strategy marker, as described on pg. 46 of the Comprehensive Rules. As such, the action does not "see" the Strategy marker as a viable marker to move the model toward.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => '4.3 – Can a model make a marker, such as a Piano marker, overlapping a Strategy marker?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes, unless the Strategy marker has the impassable terrain trait. For example, the <em>Haphazard Topography</em> action cannot target a Strategy marker, but the <em>Bombs Away</em> action can make a Piano marker overlapping a Strategy marker.</p>',
                'sort_order' => 3,
            ],
            [
                'title' => '4.4 – How thick is the centerline? If a model is touching the centerline, but not overlapping it, is it considered "completely within" a table half?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>The centerline does not have any thickness. It is a one-dimensional line that runs through the mathematical center of the table. It is not possible to "touch" the centerline without overlapping it. Thus, any model whose base "touches" the centerline is considered to be on both table halves, and not "completely within" either.</p>',
                'sort_order' => 4,
            ],
            [
                'title' => '4.5 – If a model is on top of two pieces of terrain, can it contribute toward controlling both for the Take the Highground scheme?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 5,
            ],
            [
                'title' => '4.6i – Plant Explosives: Explosive tokens cannot be removed, but can they be copied by effects like K.O.T.O.\'s Harmonize trigger?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 6,
            ],
            [
                'title' => '4.6ii – Plant Explosives: Are Explosive tokens gained "after deployment" friendly or enemy tokens?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Explosive tokens gained from setting up the encounter are enemy tokens.</p>',
                'sort_order' => 7,
            ],
            [
                'title' => '4.6iii – Plant Explosives: Does a model that deploys after deployment, such as with the From Shadows ability, gain an Explosive token when it deploys?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>No. "After deployment" does not mean "after this model is deployed," it instead refers to just after Step I: Deployment of encounters.</p>',
                'sort_order' => 8,
            ],
            [
                'title' => '4.7 – Leave Your Mark: Is it possible to block the LoS of a Scheme marker to the centerpoint, such as by having an enemy model covering the centerpoint?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 9,
            ],
            [
                'title' => '4.8 – Can a Scheme like Breakthrough be revealed at the end of a peon\'s activation, if that peon is ignored by Schemes?',
                'category' => FaqCategoryEnum::Encounters,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 10,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 5: SPECIFIC ABILITIES, ACTIONS, AND TRIGGERS
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '5.1 – Hard to Kill: Does Hard to Kill reduce the damage taken for the purposes of irreducible damage?',
                'category' => FaqCategoryEnum::SpecificAbilities,
                'answer' => '<p>No. <em>Hard to Kill</em> prevents a model\'s health from being lowered and does not reduce damage.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => '5.2 – Stealth: Can a model target a model with the Stealth ability if it is not within 6" of the model with Stealth, but is drawing range from an object that is within 6" of the model with Stealth?',
                'category' => FaqCategoryEnum::SpecificAbilities,
                'answer' => '<p>Yes. Only the object range is being drawn from needs to be within 6" of the model with <em>Stealth</em>.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => '5.3 – If a model declares the Onslaught trigger during the same action where Rasputina, Abominable uses the Absolute Zero ability, does the model still get to declare the attack generated by Onslaught?',
                'category' => FaqCategoryEnum::SpecificAbilities,
                'answer' => '<p>Yes. <em>Absolute Zero</em> ends Step C.3 (Taking Actions) of the activation phase and forces the model into Step C.4 (End Activation). It may no longer use any portion of its action limit, but there is nothing preventing models from resolving actions outside of Step C.3 (or outside of the activation phase altogether!).</p>',
                'sort_order' => 3,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 6: ARCANISTS (6.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '6.1 – When Ceddra, White Stag, uses Onward, and declares the Hunter\'s Cunning trigger, does she still get to resolve the generated Walk action after replacing?',
                'category' => FaqCategoryEnum::Arcanists,
                'answer' => '<p>Yes. Models that replace are generally considered to be the same entity, just represented by a different stat card. This also means that if White Stag uses <em>Demise (Eternal)</em>, replaces into Sightless Snow, and then replaces back into White Stag all within the same turn, <em>Demise (Eternal)</em> is not available for use again.</p>',
                'sort_order' => 1,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 8: EXPLORER'S SOCIETY (8.1–8.2)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '8.1 – If Sidir Archibal uses the Mortar Strike action and there are no other markers within 2" of the made Remains marker, does he have to remove the marker he just made?',
                'category' => FaqCategoryEnum::ExplorersSociety,
                'answer' => '<p>This is a typo. Please treat this action as if it read, "Make a Remains marker within range, then remove either a piece of destructible terrain or another marker within 2" of the made marker."</p>',
                'sort_order' => 1,
            ],
            [
                'title' => '8.2 – DUA: Does Fall Into Darkness trigger place the target in base contact with a Shadow Door marker within 6" of itself, or within 6" of the model declaring the trigger?',
                'category' => FaqCategoryEnum::ExplorersSociety,
                'answer' => '<p>As written, this wording means "within 6 inches of the model declaring the trigger". This is not the intention for this specific trigger. Expect it to be errata\'d accordingly later, and until then please play the <em>Fall Into Darkness</em> trigger as if it read, "Place the target in base contact with a Shadow Door marker within 6" of itself."</p>',
                'sort_order' => 2,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 9: GUILD (9.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '9.1 – Are Dashel Barker, The Old Guard, and Dashel Barker, Butcher unique models?',
                'category' => FaqCategoryEnum::Guild,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 1,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 10: NEVERBORN (10.1–10.3)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '10.1 – Savage: How does The Land Yields interact with the Runic Binding scheme?',
                'category' => FaqCategoryEnum::Neverborn,
                'answer' => '<p>It does not have a significant impact. If there were a single 8ss model in the area created by the Scheme markers, the Scheme would see that there are two models in the area with a total cost of 8ss.</p>',
                'sort_order' => 1,
            ],
            [
                'title' => '10.2 – Woe: Does the Feed on Paranoia ability heal the model when a Scheme marker is made as the result of a friendly Paranoia token being removed from an enemy model?',
                'category' => FaqCategoryEnum::Neverborn,
                'answer' => '<p>No.</p>',
                'sort_order' => 2,
            ],
            [
                'title' => '10.3 – Is Candy a unique model?',
                'category' => FaqCategoryEnum::Neverborn,
                'answer' => '<p>Yes.</p>',
                'sort_order' => 3,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 11: OUTCASTS (11.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '11.1 – When Zipp, Dread Pirate succeeds in the Your Money or Your Life... Or Maybe Your Hat action, can the target choose an upgrade that Zipp already has attached?',
                'category' => FaqCategoryEnum::Outcasts,
                'answer' => '<p>No. Due to the "This or That" rule, the model cannot choose an upgrade Zipp already has attached.</p>',
                'sort_order' => 1,
            ],

            // ═══════════════════════════════════════════════════════════
            //  SECTION 12: RESURRECTIONISTS (12.1)
            // ═══════════════════════════════════════════════════════════
            [
                'title' => '12.1 – Tormented: If an enemy model with the Set Adrift upgrade attached to it is controlled by a model in Jack Daw\'s crew, and it declares the Scream of the Lost trigger, can Jack Daw choose to have it kill itself?',
                'category' => FaqCategoryEnum::Resurrectionists,
                'answer' => '<p>There is no circumstance under which the enemy model could be killed by <em>Scream of the Lost</em> while controlled by a model in Jack\'s crew. The "This or That" section of the Comprehensive Rule book describes that a model cannot choose to kill itself while controlled by the opposing player. It is the same as attempting to give a Stunned token to a model that already has a Stunned token, as outlined in the example on that page.</p>',
                'sort_order' => 1,
            ],
        ];
    }
}
