<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Services\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shifts) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $openShift = $user->openShift()->first();
        $summary = $openShift ? $this->shifts->summary($openShift) : null;

        $query = CashierShift::query()->with('user')->latest('opened_at');

        if ($user->isCashier()) {
            $query->where('user_id', $user->id);
        }

        return view('shifts.index', [
            'openShift' => $openShift,
            'summary' => $summary,
            'history' => $query->paginate(10),
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ]);

        $this->shifts->open($request->user(), $validated['opening_cash']);

        return redirect()->route('shifts.index')->with('success', 'Shift dibuka. Selamat berjualan.');
    }

    public function close(Request $request, CashierShift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'closing_note' => ['nullable', 'string', 'max:500'],
        ]);

        $closed = $this->shifts->close(
            $request->user(),
            $shift,
            $validated['closing_cash'],
            $validated['closing_note'] ?? null,
        );

        $difference = (float) $closed->difference_cash;

        $message = $difference === 0.0
            ? 'Shift ditutup. Kas sesuai dengan perhitungan sistem.'
            : 'Shift ditutup. Selisih kas: '.rupiah($difference).'.';

        return redirect()->route('shifts.index')->with('success', $message);
    }
}
