@component('mail::message')
# Actualización de tu Ticket

Hola, **{{ $ticket->user->name }}**.

Te informamos que el estado de tu ticket "**{{ $ticket->title }}**" ha sido actualizado por un administrador o
supervisor.

**Nuevo estado:** {{ $ticket->status }}

@component('mail::button', ['url' => config('app.url') . '/tickets/' . $ticket->id. ''])
Ver detalles del Ticket
@endcomponent

Si tienes alguna duda, por favor contacta con soporte.

Gracias,<br>
{{ config('app.name') }}
@endcomponent