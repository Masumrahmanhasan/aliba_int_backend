@component('mail::message')
# Dear {{$notifiable->full_name}}

{!! $data !!}

Thanks,<br>
<a href="{{('/')}}">
{{ config('app.name') }}
</a>
@endcomponent
