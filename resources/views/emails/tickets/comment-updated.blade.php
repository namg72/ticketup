@component('mail::message')
# Actualización de tu Ticket

Hola, **{{ $ticket->user->name }}**.

Te informamos que los comentarios de tu ticket "**{{ $ticket->title }}**" han sido actualizados por un administrador o
supervisor.



@component('mail::button', ['url' => config('app.url') . '/tickets/' . $ticket->id. '/edit'])
Ver detalles del Ticket
@endcomponent

Si tienes alguna duda, por favor contacta con soporte.

Gracias,<br>
{{ config('app.name') }}
@endcomponent