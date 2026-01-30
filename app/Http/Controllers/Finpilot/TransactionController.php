<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SavedView;
use App\Models\Status;
use App\Models\Transaction;
use App\Support\PeriodContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function importForm(): Response
    {
        return Inertia::render('Pages/TransactionsImport', [
            'expectedColumns' => [
                'date',
                'description',
                'amount',
                'account_name',
                'category',
                'status',
                'notes',
            ],
        ]);
    }

    public function importTemplate()
    {
        $content = implode("\n", [
            'date,description,amount,account_name,category,status,notes',
            '2024-08-31,Salário,3500.00,Conta principal,Salário,Confirmado,Pagamento mensal',
            '2024-08-20,Supermercado,-250.75,Cartão,Alimentação,Confirmado,Compras do mês',
        ]);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'finpilot-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function importPreview(Request $request): Response
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $data['file'];
        $parsed = $this->parseCsvFile($file);

        $map = $this->resolveImportMap($parsed['header']);
        $rows = $parsed['rows'];
        $sampleRows = array_slice($rows, 0, 5);

        if (!$map) {
            $map = $this->defaultImportMap();
            if ($parsed['header']) {
                array_unshift($rows, $parsed['header']);
            }
        }

        $preview = [];
        $validRows = [];
        $invalidCount = 0;
        $seen = [];

        foreach ($rows as $row) {
            $validation = $this->validateRow($row, $map);
            $signature = $this->rowSignature($row, $map);
            if ($signature && array_key_exists($signature, $seen)) {
                $validation['valid'] = false;
                $validation['errors'][] = 'Duplicado';
            } else {
                $seen[$signature ?? uniqid('', true)] = true;
            }
            $preview[] = [
                'data' => $this->normalizeRowForPreview($row, $map),
                'errors' => $validation['errors'],
            ];

            if ($validation['valid']) {
                $validRows[] = $row;
            } else {
                $invalidCount += 1;
            }
        }

        $request->session()->put('finpilot.import.rows', $validRows);
        $request->session()->put('finpilot.import.map', $map);
        $request->session()->put('finpilot.import.invalid', $invalidCount);

        return Inertia::render('Pages/TransactionsImport', [
            'expectedColumns' => array_keys($this->defaultImportMap()),
            'sampleRows' => $sampleRows,
            'preview' => $preview,
            'validCount' => count($validRows),
            'invalidCount' => $invalidCount,
        ]);
    }

    public function importConfirm(Request $request): RedirectResponse
    {
        $userId = $request->user()?->id;
        $rows = $request->session()->get('finpilot.import.rows', []);
        $map = $request->session()->get('finpilot.import.map', $this->defaultImportMap());
        $invalidCount = (int) $request->session()->get('finpilot.import.invalid', 0);

        $imported = 0;
        foreach ($rows as $row) {
            $payload = $this->payloadFromRow($row, $map, $userId);
            if (!$payload) {
                $invalidCount += 1;
                continue;
            }
            Transaction::create($payload);
            $imported += 1;
        }

        $request->session()->forget(['finpilot.import.rows', 'finpilot.import.map', 'finpilot.import.invalid']);

        return redirect()->route('finpilot.transactions')
            ->with('success', "Importação concluída. {$imported} transações adicionadas. {$invalidCount} linhas inválidas ignoradas.");
    }

    public function export(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year');
        $activeMonth = $request->input('active_month', 'all');
        $period = PeriodContext::current($activeYear, $activeMonth);

        $filters = [
            'search' => $request->input('search', ''),
            'category_id' => $request->input('category_id'),
            'status_id' => $request->input('status_id'),
            'min_amount' => $request->input('min_amount'),
            'max_amount' => $request->input('max_amount'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'direction' => $request->input('direction'),
            'sort_by' => $request->input('sort_by', 'date'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
        ];

        $query = Transaction::query()->forUser($userId)->with(['category', 'status']);
        $query->whereBetween('date', [$period['start_date'], $period['end_date']]);

        if ($filters['search']) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }
        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($filters['status_id']) {
            $query->where('status_id', $filters['status_id']);
        }
        if ($filters['min_amount'] !== null && $filters['min_amount'] !== '') {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if ($filters['max_amount'] !== null && $filters['max_amount'] !== '') {
            $query->where('amount', '<=', $filters['max_amount']);
        }
        if ($filters['date_from']) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }
        if ($filters['direction'] === 'income') {
            $query->where('amount', '>=', 0);
        }
        if ($filters['direction'] === 'expense') {
            $query->where('amount', '<', 0);
        }

        $sortBy = in_array($filters['sort_by'], ['date', 'amount', 'description'], true)
            ? $filters['sort_by']
            : 'date';
        $sortDir = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';

        $rows = $query->orderBy($sortBy, $sortDir)->get();

        $filename = 'finpilot-transactions.csv';
        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['date', 'description', 'amount', 'account_name', 'category', 'status', 'notes']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->date,
                    $row->description,
                    $row->amount,
                    $row->account_name,
                    $row->category?->name,
                    $row->status?->name,
                    $row->notes,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        $activeYear = (int) $request->input('active_year');
        $activeMonth = $request->input('active_month', 'all');
        $period = PeriodContext::current($activeYear, $activeMonth);

        $views = SavedView::forUser($userId)
            ->where('entity', 'transactions')
            ->orderBy('name')
            ->get();

        $activeView = null;
        if ($request->filled('view_id')) {
            $activeView = $views->firstWhere('id', (int) $request->input('view_id'));
        }

        if (!$activeView) {
            $activeView = $views->firstWhere('is_default', true) ?? $views->first();
        }

        $defaultConfig = [
            'filters' => [
                'search' => '',
                'category_id' => null,
                'status_id' => null,
                'min_amount' => null,
                'max_amount' => null,
                'date_from' => null,
                'date_to' => null,
                'direction' => null,
            ],
            'sort' => ['by' => 'date', 'dir' => 'desc'],
            'columns' => [
                'date' => true,
                'description' => true,
                'amount' => true,
                'account_name' => true,
                'category' => true,
                'status' => true,
                'notes' => false,
            ],
        ];

        $viewConfig = $activeView?->config_json ?? $defaultConfig;
        $filters = [
            'search' => $request->input('search', $viewConfig['filters']['search'] ?? ''),
            'category_id' => $request->input('category_id', $viewConfig['filters']['category_id'] ?? null),
            'status_id' => $request->input('status_id', $viewConfig['filters']['status_id'] ?? null),
            'min_amount' => $request->input('min_amount', $viewConfig['filters']['min_amount'] ?? null),
            'max_amount' => $request->input('max_amount', $viewConfig['filters']['max_amount'] ?? null),
            'date_from' => $request->input('date_from', $viewConfig['filters']['date_from'] ?? null),
            'date_to' => $request->input('date_to', $viewConfig['filters']['date_to'] ?? null),
            'direction' => $request->input('direction', $viewConfig['filters']['direction'] ?? null),
            'sort_by' => $request->input('sort_by', $viewConfig['sort']['by'] ?? 'date'),
            'sort_dir' => $request->input('sort_dir', $viewConfig['sort']['dir'] ?? 'desc'),
        ];

        $query = Transaction::query()->forUser($userId)->with(['category', 'status']);
        $query->whereBetween('date', [$period['start_date'], $period['end_date']]);

        if ($filters['search']) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if ($filters['status_id']) {
            $query->where('status_id', $filters['status_id']);
        }

        if ($filters['min_amount'] !== null && $filters['min_amount'] !== '') {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if ($filters['max_amount'] !== null && $filters['max_amount'] !== '') {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        if ($filters['date_from']) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if ($filters['direction'] === 'income') {
            $query->where('amount', '>=', 0);
        }

        if ($filters['direction'] === 'expense') {
            $query->where('amount', '<', 0);
        }

        $sortBy = in_array($filters['sort_by'], ['date', 'amount', 'description'], true)
            ? $filters['sort_by']
            : 'date';
        $sortDir = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';

        $transactions = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pages/Transactions', [
            'transactions' => $transactions,
            'categories' => Category::forUser($userId)->orderBy('name')->get(),
            'statuses' => Status::forUser($userId)->orderBy('name')->get(),
            'views' => $views,
            'activeView' => $activeView,
            'filters' => $filters,
            'period' => [
                'activeYear' => $period['year'],
                'activeMonth' => $period['month'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status_id' => ['nullable', 'exists:statuses,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['user_id'] = $request->user()?->id;
        Transaction::create($data);

        return redirect()->back()->with('success', 'Transação criada com sucesso.');
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status_id' => ['nullable', 'exists:statuses,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $transaction->update($data);

        return redirect()->back()->with('success', 'Transação atualizada.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->back()->with('success', 'Transação removida.');
    }

    private function parseCsvFile(UploadedFile $file): array
    {
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return ['header' => null, 'rows' => []];
        }

        $delimiter = $this->detectDelimiter($lines[0]);
        $header = str_getcsv($lines[0], $delimiter);
        $rows = [];
        foreach (array_slice($lines, 1) as $line) {
            $rows[] = str_getcsv($line, $delimiter);
        }

        return ['header' => $header, 'rows' => $rows];
    }

    private function resolveImportMap(?array $header): ?array
    {
        if (!$header) {
            return null;
        }

        $normalized = array_map(fn ($value) => strtolower(trim((string) $value)), $header);
        $columns = $this->defaultImportMap();
        $map = [];
        $found = 0;

        foreach ($columns as $key => $index) {
            $headerIndex = array_search($key, $normalized, true);
            if ($headerIndex !== false) {
                $map[$key] = $headerIndex;
                $found += 1;
            }
        }

        return $found >= 2 ? $map : null;
    }

    private function defaultImportMap(): array
    {
        return [
            'date' => 0,
            'description' => 1,
            'amount' => 2,
            'account_name' => 3,
            'category' => 4,
            'status' => 5,
            'notes' => 6,
        ];
    }

    private function validateRow(array $row, array $map): array
    {
        $errors = [];
        $dateRaw = $row[$map['date']] ?? null;
        $description = $row[$map['description']] ?? null;
        $amountRaw = $row[$map['amount']] ?? null;

        if (!$dateRaw) {
            $errors[] = 'Data ausente';
        } elseif (!$this->parseDate($dateRaw)) {
            $errors[] = 'Data inválida';
        }

        if (!$description) {
            $errors[] = 'Descrição ausente';
        }

        if ($amountRaw === null || $amountRaw === '') {
            $errors[] = 'Valor ausente';
        } elseif ($this->parseAmount($amountRaw) === null) {
            $errors[] = 'Valor inválido';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    private function normalizeRowForPreview(array $row, array $map): array
    {
        return [
            'date' => $row[$map['date']] ?? '',
            'description' => $row[$map['description']] ?? '',
            'amount' => $row[$map['amount']] ?? '',
            'account_name' => $row[$map['account_name']] ?? '',
            'category' => $row[$map['category']] ?? '',
            'status' => $row[$map['status']] ?? '',
            'notes' => $row[$map['notes']] ?? '',
        ];
    }

    private function parseDate(string $dateRaw): ?string
    {
        try {
            return Carbon::parse($dateRaw)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function parseAmount(string $amountRaw): ?float
    {
        $clean = str_replace(['R$', ' ', "\u{A0}"], '', $amountRaw);
        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '.', $clean);
        }

        if (!is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    private function detectDelimiter(string $line): string
    {
        $commaCount = substr_count($line, ',');
        $semiCount = substr_count($line, ';');

        return $semiCount > $commaCount ? ';' : ',';
    }

    private function rowSignature(array $row, array $map): ?string
    {
        $date = $row[$map['date']] ?? null;
        $description = $row[$map['description']] ?? null;
        $amount = $row[$map['amount']] ?? null;

        if (!$date || !$description || $amount === null || $amount === '') {
            return null;
        }

        return strtolower(trim((string) $date)) . '|' . strtolower(trim((string) $description)) . '|' . trim((string) $amount);
    }

    private function payloadFromRow(array $row, array $map, ?int $userId): ?array
    {
        $dateRaw = $row[$map['date']] ?? null;
        $description = $row[$map['description']] ?? null;
        $amountRaw = $row[$map['amount']] ?? null;

        if (!$dateRaw || !$description || $amountRaw === null || $amountRaw === '') {
            return null;
        }

        $date = $this->parseDate((string) $dateRaw);
        if (!$date) {
            return null;
        }

        $amount = $this->parseAmount((string) $amountRaw);
        if ($amount === null) {
            return null;
        }
        $accountName = $row[$map['account_name']] ?? null;
        $categoryName = $row[$map['category']] ?? null;
        $statusName = $row[$map['status']] ?? null;
        $notes = $row[$map['notes']] ?? null;

        $categoryId = null;
        if ($categoryName) {
            $category = Category::forUser($userId)->firstOrCreate([
                'user_id' => $userId,
                'name' => trim((string) $categoryName),
            ], [
                'color' => null,
                'is_hidden' => false,
            ]);
            $categoryId = $category->id;
        }

        $statusId = null;
        if ($statusName) {
            $status = Status::forUser($userId)->firstOrCreate([
                'user_id' => $userId,
                'name' => trim((string) $statusName),
            ]);
            $statusId = $status->id;
        }

        return [
            'user_id' => $userId,
            'date' => $date,
            'description' => trim((string) $description),
            'amount' => $amount,
            'account_name' => $accountName ? trim((string) $accountName) : null,
            'category_id' => $categoryId,
            'status_id' => $statusId,
            'notes' => $notes ? trim((string) $notes) : null,
        ];
    }
}
