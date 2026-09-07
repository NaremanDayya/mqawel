<?php

namespace App\Http\Controllers\Api;

use App\Exports\ExpiredFilesReportExport;
use App\Exports\ProjectExpensesReportExport;
use App\Exports\WorkerPaymentsReportExport;
use App\Exports\WorkersReportExport;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\ProjectExpense;
use App\Models\Worker;
use App\Models\WorkerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ReportController extends Controller
{
    // ---------------------------------------------------------------
    // Workers
    // ---------------------------------------------------------------

    public function workers(Request $request)
    {
        $workers = $this->workersQuery($request)->paginate($request->integer('per_page', 20));

        return response()->json(array_merge(
            \App\Http\Resources\Api\WorkerResource::collection($workers)->response()->getData(true),
            ['stats' => $this->workersStats($request->user()->company_id)],
        ));
    }

    public function workersExport(Request $request)
    {
        $workers = $this->workersQuery($request)->get();

        if ($request->get('format') === 'excel') {
            return Excel::download(new WorkersReportExport($workers), __('backend.workers_report').'-'.date('Y-m-d H-i').'.xlsx');
        }

        return $this->pdf('exports.workers_report', ['Workers' => $workers], __('backend.workers_report'));
    }

    private function workersQuery(Request $request)
    {
        $query = Worker::where('company_id', $request->user()->company_id);

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->filled('ethnicity')) {
            $query->where('ethnicity', $request->get('ethnicity'));
        }
        if ($request->filled('job_title')) {
            $query->where('job_title', $request->get('job_title'));
        }

        return $query->latest('id');
    }

    private function workersStats(int $companyId): array
    {
        return [
            'number_of_workers' => Worker::where('company_id', $companyId)->count(),
            'number_of_ethnicities' => Worker::select('ethnicity')->where('company_id', $companyId)->whereNotNull('ethnicity')->distinct()->count('ethnicity'),
            'number_of_job_titles' => Worker::select('job_title')->where('company_id', $companyId)->whereNotNull('job_title')->distinct()->count('job_title'),
        ];
    }

    // ---------------------------------------------------------------
    // Worker payments
    // ---------------------------------------------------------------

    public function workerPayments(Request $request)
    {
        $payments = $this->workerPaymentsQuery($request)->paginate($request->integer('per_page', 20));

        return response()->json(array_merge(
            \App\Http\Resources\Api\WorkerPaymentResource::collection($payments)->response()->getData(true),
            ['stats' => $this->workerPaymentsStats($request->user()->company_id)],
        ));
    }

    public function workerPaymentsExport(Request $request)
    {
        $payments = $this->workerPaymentsQuery($request)->with('worker')->get();

        if ($request->get('format') === 'excel') {
            return Excel::download(new WorkerPaymentsReportExport($payments), __('backend.worker_payments_report').'-'.date('Y-m-d H-i').'.xlsx');
        }

        return $this->pdf('exports.worker_payments_report', ['Payments' => $payments], __('backend.worker_payments_report'));
    }

    private function workerPaymentsQuery(Request $request)
    {
        $query = WorkerPayment::where('company_id', $request->user()->company_id);

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        } else {
            $query->where('status', 'unpaid');
        }
        if ($request->filled('title')) {
            $query->where('title', $request->get('title'));
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        return $query->latest('id');
    }

    private function workerPaymentsStats(int $companyId): array
    {
        return [
            'number_of_due_workers' => WorkerPayment::select('worker_id')->where('company_id', $companyId)->where('status', 'unpaid')->distinct()->count('worker_id'),
            'paid_due_amount' => (float) WorkerPayment::where('company_id', $companyId)->where('status', 'paid')->sum('amount'),
            'unpaid_due_amount' => (float) WorkerPayment::where('company_id', $companyId)->where('status', 'unpaid')->sum('amount'),
        ];
    }

    // ---------------------------------------------------------------
    // Expired files
    // ---------------------------------------------------------------

    public function expiredFiles(Request $request)
    {
        $files = $this->expiredFilesQuery($request)->paginate($request->integer('per_page', 20));

        return response()->json(array_merge(
            \App\Http\Resources\Api\FileResource::collection($files)->response()->getData(true),
            ['stats' => $this->expiredFilesStats($request->user())],
        ));
    }

    public function expiredFilesExport(Request $request)
    {
        $files = $this->expiredFilesQuery($request)->get();

        if ($request->get('format') === 'excel') {
            return Excel::download(new ExpiredFilesReportExport($files), __('backend.expired_files_report').'-'.date('Y-m-d H-i').'.xlsx');
        }

        return $this->pdf('exports.expired_files_report', ['files' => $files], __('backend.expired_files_report'));
    }

    private function expiredFilesQuery(Request $request)
    {
        $query = File::where('company_id', $request->user()->company_id)
            ->where('expiry_date', '<', date('Y-m-d'));

        if ($request->filled('parent_table')) {
            $query->where('parent_table', $request->get('parent_table'));
        }
        if ($request->filled('year')) {
            $query->whereYear('expiry_date', (int) $request->get('year'));
        }
        if ($request->filled('month')) {
            $query->whereMonth('expiry_date', (int) $request->get('month'));
        }

        return $query->latest('id');
    }

    private function expiredFilesStats($user): array
    {
        $companyId = $user->company_id;
        $leadDays = $user->company->about_to_expire_days ?: 30;

        return [
            'number_of_files' => File::where('company_id', $companyId)->count(),
            'expired_files' => File::where('company_id', $companyId)->where('expiry_date', '<', date('Y-m-d'))->count(),
            'about_to_expire' => File::where('company_id', $companyId)
                ->where('expiry_date', '<=', now()->addDays($leadDays))
                ->where('expiry_date', '>', now())
                ->count(),
        ];
    }

    // ---------------------------------------------------------------
    // Project expenses
    // ---------------------------------------------------------------

    public function projectExpenses(Request $request)
    {
        $expenses = $this->projectExpensesQuery($request)->with('project')->paginate($request->integer('per_page', 20));

        return response()->json(array_merge(
            \App\Http\Resources\Api\ProjectExpenseResource::collection($expenses)->response()->getData(true),
            ['stats' => $this->projectExpensesStats($request->user()->company_id)],
        ));
    }

    public function projectExpensesExport(Request $request)
    {
        $expenses = $this->projectExpensesQuery($request)->with('project')->get();

        if ($request->get('format') === 'excel') {
            return Excel::download(new ProjectExpensesReportExport($expenses), __('backend.project_expenses_report').'-'.date('Y-m-d H-i').'.xlsx');
        }

        return $this->pdf('exports.project_expenses_report', ['Expenses' => $expenses], __('backend.project_expenses_report'));
    }

    private function projectExpensesQuery(Request $request)
    {
        $query = ProjectExpense::where('company_id', $request->user()->company_id);

        if ($request->filled('currency')) {
            $query->where('currency', $request->get('currency'));
        }

        return $query->latest('id');
    }

    private function projectExpensesStats(int $companyId): array
    {
        return [
            'number_of_projects_with_expenses' => ProjectExpense::select('project_id')->where('company_id', $companyId)->distinct()->count('project_id'),
            'number_of_procedures' => ProjectExpense::where('company_id', $companyId)->count(),
            'total_expenses' => (float) ProjectExpense::where('company_id', $companyId)->sum('amount'),
        ];
    }

    // ---------------------------------------------------------------
    // Shared PDF helper
    // ---------------------------------------------------------------

    private function pdf(string $view, array $data, string $filenameBase)
    {
        $html = Blade::render($view, $data);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => storage_path('app/mpdf/tmp'),
        ]);

        if (session('current_lang') === 'ar') {
            $mpdf->SetDirectionality('rtl');
        }

        $mpdf->WriteHTML($html);

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', Destination::STRING_RETURN);
        }, $filenameBase.'-'.date('Y-m-d H-i').'.pdf', ['Content-Type' => 'application/pdf']);
    }
}
