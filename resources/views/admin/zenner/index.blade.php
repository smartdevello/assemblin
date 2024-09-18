@extends('admin.layout.master')
@section('style')
    <style>
        tr.v-data-table__expanded__content>div{
            margin-top: 10px;
            margin-bottom: 10px;
            margin-left: 20px;
        }
        tr.v-data-table__expanded__content>div td.table_value{
            min-width: 110px;
        }
        tr.v-data-table__expanded__content>div tr>td{
            padding: 0 10px;
        }
        .v-data-table__expanded__content{
            height:110px;
        }
        .v-data-table__expanded__content .log_table{
            position: absolute;
        }
        div.v-data-table table thead tr th:nth-child(4), div.v-data-table table thead tr th:nth-child(6){
            min-width: 200px;
        }
    </style>
@endsection
@section('content')
@verbatim
    <v-main>
        <v-container>
            <template>
                <v-card>
                    <v-card-title>
                        Zenner Devices
                        <v-spacer></v-spacer>
                        <v-text-field
                          v-model="search"
                          append-icon="mdi-magnify"
                          label="Search"
                          single-line
                          hide-details
                        ></v-text-field>
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
                        <template v-slot:expanded-item="{ headers, item }">
                            <v-simple-table>
                                <template v-slot:default>
                                    <tbody class="log_table">
                                    <tr>
                                        <td class="table_header">DateTime<td>
                                        <td v-for="(i, val) in item.logs" :key="val" class="table_value">
                                            {{val}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table_header">Value<td>
                                        <td v-for="(i, val) in item.logs" :key="val" class="table_value">
                                            {{i}}
                                        </td>
                                    </tr>

                                    </tbody>
                                </template>
                            </v-simple-table>
                        </template>
                    </v-data-table>
                </v-card>
            </template>
        </v-container>
    </v-main>
@endverbatim
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
                areas: ( <?php echo json_encode($areas); ?> ),
                headers: [
                    { text: '', value: 'data-table-expand' },
                    {
                        text: 'Device ID',
                        align: 'start',
                        value: 'deviceId',
                    },
                    { text: 'Area', value: 'area_name'},
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
                        for (log_key in sensor.logs){
                            // console.log(log_key);
                            new_log_key = new Date(log_key + " UTC").toLocaleString();
                            sensor.logs[new_log_key] = sensor.logs[log_key];
                            delete sensor.logs[log_key];
                        }
                        console.log(sensor.logs);
                    }
                }                
            },
            methods: {

            }
        })

    </script>
@endsection
