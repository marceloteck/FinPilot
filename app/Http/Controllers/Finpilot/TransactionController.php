<?php

namespace App\Http\Controllers\Finpilot;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SavedView;
use App\Models\Status;
use App\Models\Transaction;
use App\Support\PeriodContext;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
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
}
