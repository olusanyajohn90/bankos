<?php

namespace App\Http\Controllers;

use App\Models\BureauReport;
use App\Models\Customer;
use App\Models\Loan;
use App\Services\Bureau\BureauParserFactory;
use App\Services\Bureau\InternalCreditScoreService;
use App\Services\CreditBureauService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class BureauController extends Controller
{
    public function __construct(private CreditBureauService $bureauService) {}

    // ─── Customer-centric index ───────────────────────────────────────────────

    /**
     * List customers who have bureau reports (grouped).
     */
    public function index(Request $request)
    {
        $query = Customer::whereHas('bureauReports')
            ->with(['bureauReports' => function ($q) {
                $q->whereIn('status', ['parsed', 'retrieved', 'uploaded'])
                  ->latest()
                  ->select('id', 'customer_id', 'bureau', 'status', 'credit_score',
                           'total_outstanding', 'delinquency_count', 'uploaded_at', 'retrieved_at', 'created_at');
            }])
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name',  'like', "%{$request->search}%")
                   ->orWhere('bvn',        'like', "%{$request->search}%")
                   ->orWhere('phone',      'like', "%{$request->search}%");
            }))
            ->orderBy('last_name')
            ->paginate(25)
            ->withQueryString();

        return view('bureau.customers', compact('query'));
    }

    /**
     * All bureau reports for a specific customer.
     */
    public function customerReports(Customer $customer)
    {
        $reports = BureauReport::where('customer_id', $customer->id)
            ->with('loan')
            ->latest()
            ->get();

        return view('bureau.customer_reports', compact('customer', 'reports'));
    }

    /**
     * Compute and display the internal credit report + score for a customer.
     */
    public function internalReport(Customer $customer)
    {
        $reports = BureauReport::where('customer_id', $customer->id)
            ->whereIn('status', ['parsed', 'retrieved'])
            ->whereNotNull('parsed_data')
            ->latest()
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->route('bureau.customer.reports', $customer)
                ->with('error', 'No parsed bureau reports found for this customer. Upload and parse at least one report first.');
        }

        $scoreService = new InternalCreditScoreService();
        $internal     = $scoreService->compute($reports->all());

        return view('bureau.internal_report', compact('customer', 'reports', 'internal'));
    }

    // ─── Show / analytics ─────────────────────────────────────────────────────

    public function show(BureauReport $bureauReport)
    {
        $bureauReport->load(['customer', 'loan']);
        return view('bureau.show', compact('bureauReport'));
    }

    /**
     * Full analytics view for a parsed PDF bureau report.
     */
    public function analytics(BureauReport $bureauReport)
    {
        $bureauReport->load(['customer', 'loan']);

        if (!$bureauReport->parsed_data) {
            return redirect()->route('bureau.show', $bureauReport)
                ->with('error', 'This report has not been parsed yet.');
        }

        $parsed             = $bureauReport->parsed_data;
        $subject            = $parsed['subject']              ?? [];
        $summary            = $parsed['summary']              ?? [];
        $accounts           = $parsed['accounts']             ?? [];
        $performanceSummary = $parsed['performance_summary']  ?? [];
        $aggregateSummary   = array_filter($parsed['aggregate_summary'] ?? [], fn($v, $k) => $k !== '_total', ARRAY_FILTER_USE_BOTH);
        $inquiries          = $parsed['inquiries']            ?? [];
        $reportReference    = $parsed['report_reference']     ?? $bureauReport->reference;
        $reportDate         = $parsed['report_date']          ?? $bureauReport->uploaded_at?->format('d M Y');

        $performing    = array_values(array_filter($accounts, fn($a) => ($a['status'] ?? '') === 'performing'));
        $nonPerforming = array_values(array_filter($accounts, fn($a) => ($a['status'] ?? '') === 'non_performing'));
        $closed        = array_values(array_filter($accounts, fn($a) => ($a['status'] ?? '') === 'closed'));

        $relatedReports = collect();
        if ($bureauReport->customer_id) {
            $relatedReports = BureauReport::where('customer_id', $bureauReport->customer_id)
                ->where('id', '!=', $bureauReport->id)
                ->whereIn('status', ['parsed', 'retrieved'])
                ->latest()
                ->get();
        }

        return view('bureau.analytics', compact(
            'bureauReport', 'subject', 'summary', 'accounts',
            'performing', 'nonPerforming', 'closed',
            'performanceSummary', 'aggregateSummary', 'inquiries',
            'reportReference', 'reportDate', 'relatedReports'
        ));
    }

    // ─── Upload ───────────────────────────────────────────────────────────────

    public function uploadForm()
    {
        $customers = Customer::orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        return view('bureau.upload', compact('customers'));
    }

    public function uploadProcess(Request $request)
    {
        $request->validate([
            'pdf_file'    => 'required|file|mimes:pdf|max:10240',
            'customer_id' => 'nullable|exists:customers,id',
            'loan_id'     => 'nullable|exists:loans,id',
        ]);

        $file = $request->file('pdf_file');
        $path = $file->store('bureau_reports', 'local');

        try {
            $parser  = new PdfParser();
            $pdf     = $parser->parseFile(storage_path('app/' . $path));
            $rawText = $pdf->getText();
        } catch (\Exception $e) {
            $rawText = '';
        }

        $parsedData     = null;
        $detectedBureau = 'unknown';
        if ($rawText) {
            try {
                $detectedBureau = BureauParserFactory::detect($rawText);
                $parsedData     = BureauParserFactory::parse($rawText);
            } catch (\Exception $e) {}
        }

        $bureauEnum = match($detectedBureau) {
            'firstcentral' => 'firstcentral',
            'crc'          => 'crc',
            'xds'          => 'xds',
            default        => 'crc',
        };

        $summary          = $parsedData['summary']  ?? [];
        $creditScore      = $summary['credit_score']     ?? null;
        $activeLoans      = $summary['active_accounts']  ?? 0;
        $totalOutstanding = $summary['total_balance']    ?? 0;
        $delinquencyCount = $summary['derogatory_count'] ?? $summary['delinquency_count'] ?? 0;

        $tenantId = auth()->user()->tenant_id ?? null;
        if (!$tenantId && $request->customer_id) {
            $tenantId = Customer::find($request->customer_id)?->tenant_id;
        }

        $report = BureauReport::create([
            'tenant_id'          => $tenantId,
            'customer_id'        => $request->customer_id,
            'loan_id'            => $request->loan_id,
            'bureau'             => $bureauEnum,
            'reference'          => 'BUR-PDF-' . strtoupper(Str::random(8)),
            'file_path'          => $path,
            'original_filename'  => $file->getClientOriginalName(),
            'status'             => $parsedData ? 'parsed' : 'uploaded',
            'credit_score'       => $creditScore,
            'active_loans_count' => $activeLoans,
            'total_outstanding'  => $totalOutstanding,
            'delinquency_count'  => $delinquencyCount,
            'raw_text'           => $rawText ?: null,
            'parsed_data'        => $parsedData,
            'uploaded_at'        => now(),
        ]);

        return redirect()->route('bureau.analytics', $report)
            ->with('success', 'Bureau report uploaded and analysed successfully.');
    }

    // ─── API query ────────────────────────────────────────────────────────────

    public function query(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bureau'      => 'required|in:crc,xds,firstcentral',
            'loan_id'     => 'nullable|exists:loans,id',
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $loan     = isset($data['loan_id']) ? Loan::find($data['loan_id']) : null;

        $report = $this->bureauService->query($customer, $data['bureau'], $loan);

        return redirect()->route('bureau.show', $report)
            ->with('success', 'Bureau report retrieved successfully.');
    }
}
