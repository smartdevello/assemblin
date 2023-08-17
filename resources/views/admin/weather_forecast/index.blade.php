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
                    <div>
                        <strong>Temperature </strong>
                        <span>@{{ forecast_data[0]["mts-1-1-temperature"] }}</span>
                    </div>
                    <v-card-item>
                        <v-card-subtitle>Temperature</v-card-subtitle>
                        <v-card-text>@{{ forecast_data[0]["mts-1-1-temperature"] }}</v-card-text>
                    </v-card-item>
                    <v-card-item>
                        <v-card-subtitle>Rainfall</v-card-subtitle>
                        <v-card-text>@{{ forecast_data[0]["mts-1-1-PrecipitationAmount"] }}</v-card-text>
                    </v-card-item>
                    <v-card-item>
                        <v-card-subtitle>Windspeed</v-card-subtitle>
                        <v-card-text>@{{ forecast_data[0]["mts-1-1-windspeedms"] }}</v-card-text>
                    </v-card-item>
                    <v-card-item>
                        <v-card-subtitle>Pressure</v-card-subtitle>
                        <v-card-text>@{{ forecast_data[0]["mts-1-1-pressure"] }}</v-card-text>
                    </v-card-item>
                    <v-card-item>
                        <v-card-subtitle>Humidity</v-card-subtitle>
                        <v-card-text>@{{ forecast_data[0]["mts-1-1-humidity"] }}</v-card-text>
                    </v-card-item>

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
                current_data: {},
                headers: [
                    {
                        text: 'MTU',
                        align: 'start',
                        value: 'time',
                    },
                    { text: 'Temperature', value: 'mts-1-1-temperature' },
                    { text: 'Rainfall', value: 'mts-1-1-PrecipitationAmount' },
                    { text: 'Windspeed', value: 'mts-1-1-windspeedms' },
                    { text: 'Pressure', value: 'mts-1-1-pressure' },
                    { text: 'Humidity', value: 'mts-1-1-humidity' },

                ],
            },
            mounted: function() {
                for (item of this.forecast_data) {
                    item.time = new Date(item.time * 1000).toLocaleString();
                }

            },
            methods: {

            }
        })

    </script>
@endsection
