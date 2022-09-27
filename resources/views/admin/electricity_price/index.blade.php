@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!locations">
        <v-container>
            @if( \Session::has('success') ) 
                <h3>{{ \Session::get('success') }}</h3>
            @elseif ( \Session::has('error'))
                <h3 style="color: red">{{ \Session::get('error') }}</h3>
            @else
                @if( count($errors) > 0)  
                    @foreach($errors->all() as $error)
                        <h3 style="color: red">{{ $error }}</h3>
                    @endforeach
                @endif
            @endif
            <v-row>

            </v-row>
        </v-container>
    </v-main>
@endsection

@section('script')
    <script>
        const main_vm = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: {
                drawer: true,
                mainMenu: mainMenu,
                elepricedata: ( <?php echo json_encode($forecast_data); ?> ),
            },
            mounted: function() {
                console.log(this.elepricedata);
            },            
            methods: {

            }
        })

    </script>
@endsection
