@extends('admin.layout')
@section('title', 'Tarifas')
@section('content')
    <h1>Tarifas</h1>
    <p class="sub">HH y HOT — los horarios en que aplica cada una se definen por código; los precios se editan aquí.</p>
    <div class="card">
        <table>
            <thead><tr><th>Tarifa</th><th>Prioridad</th><th>Adicional por persona</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach ($rateRules as $rule)
                    <tr>
                        <td><strong>{{ $rule->name }}</strong> — {{ $rule->description }}</td>
                        <td>{{ $rule->priority }}</td>
                        <td>${{ number_format($rule->extra_person_price, 0, ',', '.') }}</td>
                        <td><span class="pill">{{ $rule->is_active ? 'ACTIVA' : 'INACTIVA' }}</span></td>
                        <td><a class="link" href="{{ route('admin.rates.edit', $rule) }}">Editar precios</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
