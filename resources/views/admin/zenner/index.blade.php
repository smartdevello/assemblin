@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!sensors">
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
                        Zenner Devices
                        <v-spacer></v-spacer>
                    </v-card-title>

                    <v-data-table
                                :headers="headers"
                                :items="sensors"
                                :search="search"
                                :items-per-page="10"
                                :single-expand="singleExpand"
                                item-key="id"
                                multi-sort
                                show-expand
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
                sensors: ( <?php echo json_encode($sensors); ?> ),
                headers: [
                    { text: '', value: 'data-table-expand' },
                    {
                        text: 'Device ID',
                        align: 'start',
                        value: 'deviceId',
                    },
                    { text: 'Tag', value: 'tag' },
                    { text: 'Name', value: 'name' },
                    { text: 'Type', value: 'type' },
                    { text: 'Latest value', value: 'value' }
                ],
                search: '',
            },
            mounted: function() {
                for (sensor of this.sensors){
                    if (sensor.strValue) {
                        sensor.value = sensor.strValue;
                    }
                    if (sensor.logs) {
                        // console.log(sensor.deviceId)
                        sensor.logs = JSON.parse(sensor.logs.logs);
                        console.log(sensor.logs);
                        for (log_key in sensor.logs){
                            // console.log(log_key);
                            new_log_key = new Date(log_key + " UTC").toLocaleString();
                            sensor.logs[new_log_key] = sensor.logs[log_key];
                            delete sensor.logs[log_key];
                        }
                    }
                }                
            },
            methods: {

            }
        })

    </script>
@endsection
