@if(Session::has('success'))
<div class="session-alert" data-message="{{Session::get('success')}}" data-icon="success"></div>
@endif
@if(Session::has('danger'))
<div class="session-alert" data-message="{{Session::get('danger')}}" data-icon="error"></div>
@endif
