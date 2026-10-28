<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\BusinessExpense;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->query('month', now()->format('Y-m'));
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return view('expenses.index', [
            'expenses' => BusinessExpense::query()
                ->with(['user', 'shift'])
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->latest('expense_date')
                ->paginate(15)
                ->withQueryString(),
            'month' => $month,
            'total' => (string) BusinessExpense::query()
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount'),
            'cashTotal' => (string) BusinessExpense::query()
                ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
                ->where('payment_method', PaymentMethod::Cash->value)
                ->sum('amount'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'expense_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $shift = $request->user()->openShift()->first();

        $expense = BusinessExpense::create([
            'user_id' => $request->user()->id,
            'cashier_shift_id' => $shift?->id,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'expense_date' => $validated['expense_date'],
            'note' => $validated['note'] ?? null,
        ]);

        ActivityLogger::log('expense.created', $expense, 'Pengeluaran dicatat: '.$expense->description.'.', null, [
            'amount' => (string) $expense->amount,
            'payment_method' => $expense->payment_method->value,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran dicatat.');
    }
}
