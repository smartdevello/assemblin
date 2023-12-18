@extends('admin.layout.master')
@section('style')
    <style>
        .section_container {
            background: lightgray;
            padding: 10px;
            border: 1px solid;
            border-radius: 10px;
            display: inline-block;
        }

        h1.section_title {
            font-size: 25px;
            padding: 10px;
        }
        div.v-text-field__details{
            display: none !important;
        }

        .v-select__selections {
            max-width: 100px !important;
        }
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
                        Sensors
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

                            <template v-slot:item.sendToKiona="{ item }">
                                <v-simple-checkbox
                                    v-model="item.sendToKiona"
                                ></v-simple-checkbox>
                            </template>

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
            <v-row >
                <v-col cols="12" sm="10" md="10">

                </v-col>
                <v-col cols="12" sm="2" md="2">
                    <v-btn class="blue text-center white--text mt-10" :loading="is_relation_updating" :disabled="is_relation_updating" outlined @click="update_All">Update</v-btn>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
    @endverbatim
@endsection

@section('script')
    <script>
        var token = '{!! csrf_token() !!}';
        var sensors_raw = ( <?php echo json_encode($sensors); ?> );
        console.log('sensors_raw', sensors_raw);
        for (let sensor of sensors_raw) {
            if (sensor.visibility == 1) sensor.visibility = true;
            else sensor.visibility = false;

            if (sensor.sendToKiona == 1) sensor.sendToKiona = true;
            else sensor.sendToKiona = false;
            
        }

        const main_vm = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: {
                drawer: true,
                mainMenu: mainMenu,
                sensors: [...sensors_raw ],
                singleExpand: true,
                old_sensors: [],
                tab: null,
                page: null,
                points: ( <?php echo json_encode($points); ?> ),
                controllers: ( <?php echo json_encode($controllers); ?> ),
                areas: ( <?php echo json_encode($areas); ?> ),
                is_relation_updating: false,
                update_dashboard_url : `${prefix_link}/dashboard/update`,
                send_data_url : `${base_url}/point/writePointsbyid`,

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
                    { text: 'Latest value', value: 'value' },
                    { text: 'Send to Kiona', value: 'sendToKiona' },
                ],
                search: '',

            },
            ready: function() {

            },
            mounted: function() {

                for (sensor of this.sensors){
                    if (sensor.logs) {
                        // console.log(sensor.deviceId)
                        sensor.logs = JSON.parse(sensor.logs.logs);
                        // console.log(sensor.logs);
                        for (log_key in sensor.logs){
                            // console.log(log_key);
                            new_log_key = new Date(log_key + " UTC").toLocaleString();
                            sensor.logs[new_log_key] = sensor.logs[log_key];
                            delete sensor.logs[log_key];
                        }
                    }
                }
                
                this.update_oldData();
            },
            watch: {
            },
            methods: {

                changeContoller: function (controller_id, sensor_id) {

                },

                update_oldData: function(){
                    this.old_sensors = [];
                    for (const sensor of this.sensors) {
                        point_name = null;
                        if ( sensor.point_id ) {
                            point = this.points.find(point => point.id == sensor.point_id);
                            point_name = point.name;
                        }
                        this.old_sensors.push({
                                "id" : sensor.id,
                                "name" : sensor.name,
                                "value" : String(sensor.value),
                                "point_id" : sensor.point_id,
                                "point_name" : point_name ?? null,
                                "controller_id" : sensor.controller_id,
                                "area_id" : sensor.area_id,
                                "visibility" : sensor.visibility,
                                "sendToKiona" : sensor.sendToKiona,
                        });
                    }

                },
                update_All: function(){
                    this.is_relation_updating = true;
                    let submitdata = [];
                    this.sensors.sort((a, b) =>  a.id - b.id );
                    this.old_sensors.sort((a, b) => a.id - b.id  );

                    for (let [i,  sensor] of this.sensors.entries())
                    {
                        point_name = null;
                        if ( sensor.point_id ) {
                            point = this.points.find(point => point.id == sensor.point_id);
                            point_name = point.name;
                        }

                        if (this.old_sensors[i].name != sensor.name  ||  this.old_sensors[i].value != sensor.value || this.old_sensors[i].point_id != sensor.point_id || this.old_sensors[i].visibility != sensor.visibility) {
                            submitdata.push({
                                "id" : sensor.id,
                                "name" : sensor.name,
                                "value" : String(sensor.value),
                                "point_id" : sensor.point_id,
                                "point_name" : point_name ?? null,
                                "controller_id" : sensor.controller_id,
                                "area_id" : sensor.area_id,
                                "visibility" : sensor.visibility,
                                "sendToKiona" : sensor.sendToKiona,
                            });
                        }

                    }
                    console.log(submitdata);
                    if (submitdata.length == 0) {
                        toastr.error('Nothing to update');
                        this.is_relation_updating = false;
                        return;
                    }
                    var settings = {
                            "url": this.update_dashboard_url,
                            "method": "POST",
                            "timeout": 0,
                            "headers": {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": token,
                            },
                            "data": JSON.stringify(submitdata),
                    };
                    var update_raw = this.update_raw;
                    $.ajax(settings).done(function(response) {

                            main_vm.is_relation_updating = false;
                            toastr.options = {
                                "closeButton": false,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": false,
                                "positionClass": "toast-bottom-center",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            };
                            toastr.success('Updated Successfully');
                            console.log(response);
                            main_vm.update_oldData();
                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            main_vm.is_relation_updating = false;
                            toastr.error('Something went wrong');
                            console.log(jqXHR);
                            console.log(textStatus);
                            console.log(errorThrown);
                        });

                },

            },
            computed: {

            }
        });

    </script>
@endsection
