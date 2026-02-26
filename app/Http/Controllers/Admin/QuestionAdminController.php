<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Approvals\CreateApprovalAction;
use App\Enums\MessageTypeEnum;
use App\Enums\QuestionSectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\IndexListResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SeasonListResource;
use App\Http\Resources\SectionListResource;
use App\Models\Batch;
use App\Models\Index;
use App\Models\Page;
use App\Models\Question;
use App\Models\Season;
use App\Models\Section;
use App\Services\ContentBuilder\ContentBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionAdminController extends Controller
{
    public function list(Request $request)
    {
        return SeasonListResource::collection(
            Season::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
        )->toArray($request);
    }

    public function preview(Request $request)
    {
        $section = $request->get('section') ?? '';
        $question = $request->get('question') ?? null;
        $answer = $request->get('answer') ?? null;
        $changeNotes = $request->get('change_notes') ?? null;

        return [
            'section' => $section,
            'question' => $question ? (new ContentBuilder($question))->getFullyHydratedContent() : null,
            'answer' => $answer ? (new ContentBuilder($answer))->getFullyHydratedContent() : null,
            'change_notes' => $changeNotes ? (new ContentBuilder($changeNotes))->getFullyHydratedContent() : null,
        ];
    }

    public function view(Request $request, Question $question)
    {
        $question->loadMissing('newestVersion', 'publishedBy');

        $faq = (new ContentBuilder($question->question ?? ''))->getFullyHydratedContent();
        $answer = (new ContentBuilder($question->answer ?? ''))->getFullyHydratedContent();

        return [
            'question' => $faq,
            'answer' => $answer,
            'section' => $question->section,
            'published_at' => $question->published_at?->format('m-d-Y'),
            'published_by' => $question->publishedBy?->name,
        ];
    }

    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Questions/Index', [
            'questions' => Question::with('approval', 'batch')
                ->orderBy('id', 'DESC')
                ->get(),
        ]);
    }

    public function create(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Questions/QuestionForm', [
            'question_sections' => QuestionSectionEnum::toSelectOptions(),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'indices' => IndexListResource::collection(
                Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'sections' => SectionListResource::collection(
                Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'pages' => PageListResource::collection(
                Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function edit(Request $request, Question $question): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Admin/Questions/QuestionForm', [
            'question' => $question->loadMissing('approval'),
            'batches' => Batch::unpublished()->orderBy('id', 'desc')->get(),
            'indices' => IndexListResource::collection(
                Index::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'sections' => SectionListResource::collection(
                Section::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
            'pages' => PageListResource::collection(
                Page::orderBy('title', 'ASC')->orderBy('id', 'DESC')->get()
            )->toArray($request),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $question = $this->validateAndSave($request);

        return to_route('admin.questions.index')->withMessage($question->title.' created successfully!');
    }

    public function update(Request $request, Question $question): \Illuminate\Http\RedirectResponse
    {
        $question = $this->validateAndSave($request, $question);

        return to_route('admin.questions.index')->withMessage($question->title.' updated successfully!');
    }

    public function delete(Request $request, Question $question): \Illuminate\Http\RedirectResponse
    {
        $name = $question->title;

        $question->delete();

        return to_route('admin.questions.index')->withMessage($name.' has been deleted.');
    }

    public function publish(Request $request, Question $question): \Illuminate\Http\RedirectResponse
    {
        try {
            $question->publish($request->user());
        } catch (\Exception $exception) {
            return redirect()->back()->withMessage($exception->getMessage(), messageType: MessageTypeEnum::destructive);
        }

        return to_route('admin.questions.index')->withMessage($question->title.' has been published!');
    }

    private function validateAndSave(Request $request, ?Question $question = null): Question
    {
        $validated = $request->validate([
            'section' => ['required', 'string', 'max:255', Rule::enum(QuestionSectionEnum::class)],
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'change_notes' => ['nullable', 'string'],
            'batch_id' => ['nullable', 'int', 'exists:batches,id'],
            'publish_directly' => ['required', 'boolean'],
            'approve_directly' => ['required', 'boolean'],
        ]);

        $publishDirectly = $validated['publish_directly'];
        unset($validated['publish_directly']);
        $approveDirectly = $validated['approve_directly'];
        unset($validated['approve_directly']);
        $changeNotes = preg_replace("/(\r|\n)/", '', nl2br($validated['change_notes']));
        unset($validated['change_notes']);

        if ($validated['question']) {
            $validated['question'] = preg_replace("/(\r|\n)/", '', nl2br($validated['question']));
        }

        if ($validated['answer']) {
            $validated['answer'] = preg_replace("/(\r|\n)/", '', nl2br($validated['answer']));
        }

        if (! $question) {
            $sectionNumber = Question::section(QuestionSectionEnum::from($validated['section']))->count() + 1;
            $validated['section_number'] = $sectionNumber;

            $question = Question::create($validated);
        } else {
            $question->loadMissing('approval');

            if (! $question->published_at) {
                $question->update($validated);
                $question->approval?->delete();
            } else {
                $validated['previous'] = $question->id;
                $validated['original'] = $question->original ?? $question->id;
                $question = Question::create($validated);
            }
        }

        CreateApprovalAction::handle(
            $question->refresh(),
            $request->user(),
            changeNotes: $changeNotes,
            approveDirectly: $approveDirectly
        );

        if ($publishDirectly) {
            self::publish($request, $question->refresh());
        }

        return $question;
    }
}
