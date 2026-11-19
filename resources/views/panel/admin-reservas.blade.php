@extends('layouts.admin')

@section('titulo', 'Reservas — Panel del administrador | Explora RD')
@section('seccion', 'reservas')

@section('panel')
<div class="caja-panel">
    <h2 class="caja-lateral-titulo">Supervisión de reservas</h2>
    <p class="small text-muted">Cada agencia confirma o rechaza sus reservas. Aquí puedes ver todas para supervisar.</p>

    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-sm-4">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach (\App\Models\Reserva::ESTADOS as $e)
                    <option value="{{ $e }}" @selected($estado === $e)>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-5">
            <label for="agencia" class="form-label">Agencia</label>
            <select id="agencia" name="agencia" class="form-select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($agencias as $a)
                    <option value="{{ $a->id }}" @selected($agenciaId === $a->id)>{{ $a->nombreVisible() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3">
            <a href="{{ route('admin.reservas') }}" class="btn btn-contorno w-100">Quitar filtros</a>
        </div>
    </form>

    @if ($reservas->isEmpty())
        <div class="aviso-vacio"><i class="bi bi-calendar"></i><p>No hay reservas con esos filtros.</p></div>
    @else
        <div class="table-responsive">
            <table class="table tabla-panel align-middle mb-0">
                <thead>
                    <tr><th>#</th><th>Turista</th><th>Excursión</th><th>Agencia</th><th>Fecha</th><th>Personas</th><th>Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @foreach ($reservas as $reserva)
                        <tr>
                            <td>{{ $reserva->id }}</td>
                            <td>{{ $reserva->usuario->nombre }}</td>
                            <td>{{ $reserva->excursion->titulo }}</td>
                            <td>{{ $reserva->excursion->agencia->nombreVisible() }}</td>
                            <td class="text-nowrap">{{ $reserva->fecha->format('d/m/Y') }}</td>
                            <td>{{ $reserva->cantidad_personas }}</td>
                            <td class="text-nowrap">{{ $reserva->totalFormateado() }}</td>
                            <td>@include('panel._estado', ['estado' => $reserva->estado])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">{{ $reservas->links() }}</div>
    @endif
</div>
@endsection
