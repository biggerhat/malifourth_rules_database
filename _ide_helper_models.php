<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $approvable_type
 * @property int $approvable_id
 * @property string|null $change_notes
 * @property string|null $searchable_text
 * @property string|null $internal_notes
 * @property int|null $initiated_by
 * @property string|null $approved_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $approvable
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Batch> $batches
 * @property-read int|null $batches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Index> $indices
 * @property-read int|null $indices_count
 * @property-read \App\Models\User|null $initiatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $pages
 * @property-read int|null $pages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Scheme> $schemes
 * @property-read int|null $schemes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Season> $seasons
 * @property-read int|null $seasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Strategy> $strategies
 * @property-read int|null $strategies_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval unapproved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereChangeNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereInitiatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Approval withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperApproval {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $release_notes
 * @property string|null $searchable_text
 * @property string|null $internal_notes
 * @property string|null $published_at
 * @property int|null $published_by
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Index> $indices
 * @property-read int|null $indices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Page> $pages
 * @property-read int|null $pages_count
 * @property-read \App\Models\User|null $publishedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Scheme> $schemes
 * @property-read int|null $schemes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Season> $seasons
 * @property-read int|null $seasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Strategy> $strategies
 * @property-read int|null $strategies_count
 * @method static \Database\Factories\BatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereReleaseNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Batch withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBatch {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property \App\Enums\IndexTypeEnum $type
 * @property string|null $image
 * @property string|null $content
 * @property string|null $searchable_text
 * @property string|null $internal_notes
 * @property int|null $batch_id
 * @property int|null $previous
 * @property int|null $original
 * @property int|null $newest
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Index|null $newestVersion
 * @property-read Index|null $originalVersion
 * @property-read Index|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\IndexFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereNewest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index wherePrevious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Index withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperIndex {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string $slug
 * @property string|null $left_column
 * @property string|null $right_column
 * @property string|null $searchable_text
 * @property string|null $internal_notes
 * @property int $page_number
 * @property string|null $book_page_numbers
 * @property int|null $batch_id
 * @property int|null $previous
 * @property int|null $original
 * @property int|null $newest
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Page|null $newestVersion
 * @property-read Page|null $originalVersion
 * @property-read Page|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\PageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereBookPageNumbers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereLeftColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereNewest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePageNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePrevious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereRightColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPage {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int $season_id
 * @property string|null $prerequisites
 * @property string|null $reveal
 * @property string|null $scoring
 * @property string|null $additional
 * @property string|null $searchable_text
 * @property string $front_image
 * @property string|null $back_image
 * @property string|null $combination_image
 * @property int|null $next_scheme_1
 * @property int|null $next_scheme_2
 * @property int|null $next_scheme_3
 * @property string|null $internal_notes
 * @property int|null $batch_id
 * @property int|null $previous
 * @property int|null $original
 * @property int|null $newest
 * @property string|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Scheme|null $newestVersion
 * @property-read Scheme|null $originalVersion
 * @property-read Scheme|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\SchemeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereAdditional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereBackImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereCombinationImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereFrontImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereNewest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereNextScheme1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereNextScheme2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereNextScheme3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme wherePrerequisites($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme wherePrevious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereReveal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereScoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereSeasonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Scheme withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperScheme {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $content
 * @property string|null $searchable_text
 * @property string|null $url
 * @property string|null $internal_notes
 * @property int|null $batch_id
 * @property string|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Season|null $newestVersion
 * @property-read Season|null $originalVersion
 * @property-read Season|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\SeasonFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Season withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSeason {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string|null $title
 * @property string $slug
 * @property string|null $content
 * @property string|null $searchable_text
 * @property string|null $internal_notes
 * @property int|null $batch_id
 * @property int|null $previous
 * @property int|null $original
 * @property int|null $newest
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Section|null $newestVersion
 * @property-read Section|null $originalVersion
 * @property-read Section|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\SectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereNewest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section wherePrevious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSection {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $suit
 * @property int $season_id
 * @property string|null $setup
 * @property string|null $rules
 * @property string|null $scoring
 * @property string|null $additional
 * @property string|null $searchable_text
 * @property string $front_image
 * @property string|null $back_image
 * @property string|null $combination_image
 * @property string|null $internal_notes
 * @property int|null $batch_id
 * @property int|null $previous
 * @property int|null $original
 * @property int|null $newest
 * @property string|null $published_at
 * @property int|null $published_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Approval|null $approval
 * @property-read \App\Models\Batch|null $batch
 * @property-read Strategy|null $newestVersion
 * @property-read Strategy|null $originalVersion
 * @property-read Strategy|null $previousVersion
 * @property-read \App\Models\User|null $publishedBy
 * @method static \Database\Factories\StrategyFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy unpublished()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereAdditional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereBackImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereCombinationImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereFrontImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereNewest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy wherePrevious($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy wherePublishedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereScoring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereSearchableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereSeasonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereSetup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereSuit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Strategy withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperStrategy {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

