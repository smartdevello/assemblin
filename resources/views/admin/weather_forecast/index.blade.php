@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!forecast_data">
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
                        Weather Forcast
                        <v-spacer></v-spacer>
                    </v-card-title>
                    <v-data-table
                        :headers="headers"
                        :items="forecast_data"
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
                forecast_data: ( <?php echo json_encode($forecast_data); ?> ),
                headers: [
                    {
                        text: 'MTU',
                        align: 'start',
                        value: 'time',
                    },
                    { text: 'Temperature', value: 'mts-1-1-temperature' },
                    { text: 'PrecipitationAmount', value: 'mts-1-1-PrecipitationAmount' },
                    { text: 'Windspeed', value: 'mts-1-1-windspeedms' },
                    { text: 'Pressure', value: 'mts-1-1-pressure' },
                    { text: 'Humidity', value: 'mts-1-1-humidity' },

                ],
            },
            mounted: function() {
                // for (item of this.elecpricedata) {
                //     item.time = new Date(item.time * 1000).toLocaleString();
                // }
            },
            methods: {

            }
        })

    </script>
@endsection
