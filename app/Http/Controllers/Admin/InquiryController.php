<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inquiry;
use App\Services\InquiryEmailService;
use Illuminate\Http\Request;

class InquiryController extends AdminController
{
    public function __construct(private InquiryEmailService $inquiryEmailService)
    {
    }

    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $status = $request->query('status');

        $inquiries = Inquiry::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totals = Inquiry::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'status' => $status,
            'totals' => $totals,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:green,yellow,red'],
            'flags' => ['nullable', 'string'],
        ]);

        $parsedFlags = $this->parseFlags($data['flags'] ?? '');

        // Preserve existing flags structure (explanations, score, colors) and update only the flags array
        $existingFlags = $inquiry->flags ?? [];
        if (!is_array($existingFlags)) {
            $existingFlags = [];
        }

        // Update flags structure while preserving other data
        $flagsToSave = array_merge([
            'flags' => [],
            'explanations' => [],
            'score' => null,
            'colors' => null,
        ], $existingFlags);

        $flagsToSave['flags'] = $parsedFlags;

        $inquiry->status = $data['status'];
        $inquiry->flags = $flagsToSave;
        $inquiry->save();

        return back()->with('status', 'Inquiry updated successfully.');
    }

    public function sendEmail(Inquiry $inquiry): \Illuminate\Http\RedirectResponse
    {
        try {
            $this->inquiryEmailService->send($inquiry);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'email' => 'Unable to send email. Please check the logs for more details.',
            ]);
        }

        return back()->with('status', 'Email sent to ' . $inquiry->email . '.');
    }

    public function destroy(Inquiry $inquiry): \Illuminate\Http\RedirectResponse
    {
        $inquiry->delete();

        return back()->with('status', 'Inquiry deleted.');
    }

    private function parseFlags(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value);

        return array_values(array_filter(array_map('trim', $lines)));
    }
}
