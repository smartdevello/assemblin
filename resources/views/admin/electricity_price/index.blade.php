@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!elecpricedata">
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
            <template>
                <v-card>
                    <v-card-title>
                        Electricity Price
                        <v-spacer></v-spacer>
                    </v-card-title>
                    <v-data-table
                        :headers="headers"
                        :items="elecpricedata"
                        :items-per-page="20"
                        item-key="time"
                        :footer-props="{
                            showFirstLastPage: true,
                            firstIcon: 'mdi-arrow-collapse-left',
                            lastIcon: 'mdi-arrow-collapse-right',
                            prevIcon: 'mdi-minus',
                            nextIcon: 'mdi-plus'
                        }"
                    >

                    </v-data-table>
                </v-card>
            </template>
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
                elecpricedata: ( <?php echo json_encode($forecast_data); ?> ),
                headers: [
                    {
                        text: 'MTU',
                        align: 'start',
                        value: 'time',
                    },
                    { text: 'Day-ahead Price (EUR/MWh)', value: 'value' },
                ],
            },
            mounted: function() {
                console.log(this.elecpricedata);
            },            
            methods: {

            }
        })

    </script>
@endsection
