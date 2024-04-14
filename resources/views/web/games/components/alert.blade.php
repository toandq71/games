<div class="row">
  <div class="col-sm-12">
    @if (session('status'))
    <div class="alert bg-success alert-dismissible mb-2">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
      </button>
      {{ session('status') }}
    </div>
    @endif
    @if (session('status-fail') && !empty(session('status-fail')))
    @php
    @endphp
    <div class="alert bg-danger alert-dismissible mb-2">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
      </button>
      {!! session('status-fail') !!}
    </div>
    @endif
    @if( isset($message) && !empty($message) )
      <div class="alert bg-danger alert-dismissible fade in" role="alert">
        {{$message}}
      </div>
    @endif
    @if(isset($errors) && !is_array($errors) && $errors->all())
    <div class="alert bg-danger alert-dismissible mb-2" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
      </button>
      <ul style=" padding: 0px; background: none; box-shadow: none; margin: 0px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>
</div>
  