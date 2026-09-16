@extends('admin.layout')
@section('title', 'Personal')
@section('content')
    <h1>Personal</h1>
    <p class="sub">Anfitriones, mucamas y cualquier otro rol -- se usa para armar los turnos.</p>

    <div class="card">
        <h2>Agregar persona</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}">
            @csrf
            <div class="row2">
                <div>
                    <label>Nombre</label>
                    <input name="name" maxlength="100" required placeholder="Ej. Leonardo">
                </div>
                <div>
                    <label>Rol</label>
                    <input name="role" maxlength="50" required placeholder="Ej. Anfitrión, Mucama" list="existing-roles">
                    <datalist id="existing-roles">
                        @foreach ($staff->pluck('role')->unique() as $role)
                            <option value="{{ $role }}">
                        @endforeach
                    </datalist>
                </div>
            </div>
            <div class="actions" style="margin-top:16px;"><button class="btn" type="submit">Agregar</button></div>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Nombre</th><th>Rol</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($staff as $person)
                    <tr>
                        <td>{{ $person->name }}</td>
                        <td>{{ $person->role }}</td>
                        <td><span class="pill">{{ $person->is_active ? 'ACTIVO' : 'INACTIVO' }}</span></td>
                        <td>
                            <details>
                                <summary>Editar</summary>
                                <form method="POST" action="{{ route('admin.staff.update', $person) }}">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $person->name }}" maxlength="100" required>
                                    <input name="role" value="{{ $person->role }}" maxlength="50" required>
                                    <select name="is_active">
                                        <option value="1" @selected($person->is_active)>Activo</option>
                                        <option value="0" @selected(!$person->is_active)>Inactivo</option>
                                    </select>
                                    <button class="btn secondary" type="submit" style="padding:6px 12px; font-size:12.5px;">Guardar</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="color:#666;">Todavía no hay nadie cargado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a class="link" href="{{ route('admin.shifts.index') }}">→ Ir al panel de turnos</a>
@endsection
