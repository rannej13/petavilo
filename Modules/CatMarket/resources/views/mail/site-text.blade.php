{!! $site !!}

{!! $heading !!}

@foreach($lines as $line)
{!! $line !!}
@endforeach
@if($buttonUrl)

{!! $buttonLabel ?: 'Open '.$site !!}: {!! $buttonUrl !!}
@endif
@if($footer)

{!! $footer !!}
@endif
