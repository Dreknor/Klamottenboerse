@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="app-card p-4">
        <h4 class="mb-4">Audit-Log</h4>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Zeitpunkt</th>
                    <th>Benutzer</th>
                    <th>Aktion</th>
                    <th>Betroffenes Objekt</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                        <td><code>{{ json_encode($log->changes) }}</code></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Keine Einträge vorhanden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $logs->links() }}
    </div>
</div>
@endsection
