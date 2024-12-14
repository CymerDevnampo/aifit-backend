@extends('layouts.app')

@section('content')
<div class="container">
    <div id="app">
        <recipes-component></recipes-component>
    </div>
    {{-- <a href="/edit" class="btn btn-primary">asdasdas</a> --}}

    {{-- <button class="btn btn-primary" onclick="edit()">Button</button> --}}
</div>
@endsection

{{-- <script>
    function edit(){
        window.location.href = '/cymer';
    }
</script> --}}