<?php

namespace App\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HandlesBulkActions
{
    abstract protected function bulkModel(): string;

    abstract protected function bulkLabel(): string;

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = $this->bulkModel()::with('approval')->whereIn('id', $validated['ids'])->get();
        $count = 0;

        foreach ($items as $item) {
            if ($item->approval && ! $item->approval->approved_at) {
                $item->approval->update([
                    'approved_at' => now(),
                    'approved_by' => $request->user()->id,
                ]);
                $count++;
            }
        }

        return redirect()->back()->withMessage("{$count} {$this->bulkLabel()} approved.");
    }

    public function bulkPublish(Request $request): RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $items = $this->bulkModel()::with('approval')->whereIn('id', $validated['ids'])->get();
        $count = 0;
        $errors = [];

        foreach ($items as $item) {
            try {
                $item->publish($request->user());
                $count++;
            } catch (\Exception $e) {
                $errors[] = $item->title.': '.$e->getMessage();
            }
        }

        $message = "{$count} {$this->bulkLabel()} published.";
        if (! empty($errors)) {
            $message .= ' Errors: '.implode('; ', $errors);
        }

        return redirect()->back()->withMessage($message);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        $count = $this->bulkModel()::whereIn('id', $validated['ids'])->whereNull('published_at')->delete();

        return redirect()->back()->withMessage("{$count} {$this->bulkLabel()} deleted.");
    }
}
