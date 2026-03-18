<?php

namespace App\Http\Controllers;

use App\Models\PostingFile;
use App\Services\PostingFileService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostingFileController extends Controller
{
    public function __construct(private PostingFileService $service) {}

    public function index()
    {
        $files = PostingFile::with('uploadedBy')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('posting_files.index', compact('files'));
    }

    public function create()
    {
        return view('posting_files.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480',
            'type' => 'required|in:repayment,deposit,disbursement',
        ]);

        $uploaded  = $request->file('file');
        $fileName  = $uploaded->getClientOriginalName();
        $path      = $uploaded->store('posting-files', 'local');
        $reference = 'PF-' . strtoupper(Str::random(8));

        $file = PostingFile::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'uploaded_by' => auth()->id(),
            'file_name'   => $fileName,
            'file_path'   => $path,
            'reference'   => $reference,
            'type'        => $request->type,
            'status'      => 'pending',
        ]);

        // Validate synchronously (for MVP; production would use a queue job)
        $fullPath = storage_path('app/' . $path);
        $this->service->validateAndStore($file, $fullPath);

        return redirect()->route('posting-files.show', $file)
            ->with('success', 'File uploaded and validated. Review results below.');
    }

    public function show(PostingFile $postingFile)
    {
        $postingFile->load('uploadedBy');
        $records = $postingFile->records()->orderBy('row_number')->paginate(50);

        return view('posting_files.show', compact('postingFile', 'records'));
    }

    public function post(PostingFile $postingFile)
    {
        if ($postingFile->status !== 'validated') {
            return back()->with('error', 'File must be in "validated" status before posting.');
        }

        if ($postingFile->valid_records === 0) {
            return back()->with('error', 'No valid records to post.');
        }

        $this->service->post($postingFile);

        return redirect()->route('posting-files.show', $postingFile)
            ->with('success', "Posting complete. {$postingFile->fresh()->posted_records} records posted.");
    }

    public function downloadTemplate()
    {
        $csv = "identifier_type,identifier_value,amount,transaction_date,payment_channel,narration\n";
        $csv .= "LOAN_ACCOUNT_NUMBER,LN-00001,5000,2026-03-13,mobile,March repayment\n";
        $csv .= "BVN,12345678901,3000,2026-03-13,,\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="posting_template.csv"',
        ]);
    }
}
