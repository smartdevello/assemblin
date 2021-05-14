@extends('admin.layout.master');
@section('content');

@endsection

@section('script');
<script>
    const main_vm = new Vue({
        el: '#app',
        data: {
            drawer: true,
            mainMenu: mainMenu,

        },
        methods: {

        },
    })
</script>
@endsection