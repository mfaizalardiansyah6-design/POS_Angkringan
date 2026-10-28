@extends('layouts.app')

@section('title', 'Jejak aktivitas')

@section('content')
    <x-page-header
        eyebrow="Sistem"
        title="Jejak aktivitas."
        description="Riwayat perubahan penting untuk audit dan penelusuran."
    />

    <form method="GET" action="{{ route('activity.index') }}" class="filter-bar reveal" style="margin-top: 16px;">
        <div class="search-input">
            <x-icon name="search" />
            <input
                type="search"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                class="input"
                placeholder="Cari deskripsi atau subjek…"
                autocomplete="off"
            >
        </div>

        <div class="field" style="max-width: 175px;">
            <label for="action" class="label">Aksi</label>
            <select id="action" name="action" class="select">
                <option value="">Semua aksi</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                        {{ $action }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn--primary btn--sm" style="height: 44px;">
            <x-icon name="filter" />
            Terapkan
        </button>

        <a href="{{ route('activity.index') }}" class="btn btn--ghost btn--sm" style="height: 44px;">Reset</a>
    </form>

    <div class="panel reveal-delay" style="margin-top: 16px;">
        <div class="panel__head">
            <h2 class="panel__title">Riwayat aktivitas</h2>
            <span class="panel__meta">{{ $logs->total() }} entri</span>
        </div>

        @if ($logs->isEmpty())
            <x-empty-state
                icon="history"
                title="Belum ada aktivitas"
                description="Log aktivitas akan muncul setelah ada perubahan data penting."
            />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Subjek</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    <div class="table__primary">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="table__sub">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td>{{ $log->user?->name ?? '—' }}</td>
                                <td>
                                    <span class="mono tiny">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if ($log->subject_type)
                                        <div class="tiny muted">{{ class_basename($log->subject_type) }}</div>
                                        @if ($log->subject_id)
                                            <div class="mono tiny">#{{ $log->subject_id }}</div>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $log->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 12px 16px; border-top: 1px solid var(--color-line);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection

