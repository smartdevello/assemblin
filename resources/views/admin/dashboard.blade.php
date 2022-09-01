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
                    <v-tabs v-model="tab">
                        <v-tab>
                            Active
                        </v-tab>
                        <v-tab>
                            Hidden
                        </v-tab>
                    </v-tabs>
                    <v-tabs-items v-model="tab" class="pt-4">
                        <v-tab-item>
                            <v-data-table
                                :headers="headers"
                                :items="active_sensors"
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

                            <template v-slot:item.name="{ item }">

                                <v-text-field v-model="item.name" solo></v-text-field>

                            </template>


                                <template v-slot:item.value="{ item }">

                                    <v-text-field v-model="item.value" solo></v-text-field>

                                </template>


                                <template v-slot:item.point_id="{ item }">
                                    <v-select class="pa-0 ma-0" :items="points" v-model="item.point_id" item-text="name" item-value="id" solo @change="changePoint($event, item.id)">
                                    </v-select>
                                </template>
                                <template v-slot:item.controller_id="{ item }">
                                    <v-select :items="controllers" v-model="item.controller_id" item-text="name" item-value="id" solo @change="changeContoller($event, item.id)">
                                    </v-select>
                                </template>
                                <template v-slot:item.area_id="{ item }">
                                    <v-select :items="areas" v-model="item.area_id" item-text="name" item-value="id" solo>
                                    </v-select>
                                </template>


                                <template v-slot:item.visibility="{ item }">
                                    <v-simple-checkbox
                                    v-model="item.visibility"
                                    ></v-simple-checkbox>
                                </template>

                                <template v-slot:expanded-item="{ headers, item }">
                                    <table>
                                        <tbody>
                                            <tr v-for="(info, i) in item.logs" :key="i">
                                                <th scope="row">{{ info  }}</th> 
                                                <td scope="row">{{  i }}</td> 
                                            </tr>
                                        </tbody>
                                    <table>

                                  </template>

                            </v-data-table>
                        </v-tab-item>
                        <v-tab-item>
                            <v-data-table
                                :headers="headers"
                                :items="hidden_sensors"
                                :search="search"
                                :items-per-page="10"
                                multi-sort
                                :footer-props="{
                                    showFirstLastPage: true,
                                    firstIcon: 'mdi-arrow-collapse-left',
                                    lastIcon: 'mdi-arrow-collapse-right',
                                    prevIcon: 'mdi-minus',
                                    nextIcon: 'mdi-plus'
                                }"
                            >

                            <template v-slot:item.name="{ item }">

                                <v-text-field v-model="item.name" solo></v-text-field>

                            </template>



                                <template v-slot:item.value="{ item }">

                                    <v-text-field v-model="item.value" solo></v-text-field>

                                </template>


                                <template v-slot:item.point_id="{ item }">
                                    <v-select class="pa-0 ma-0" :items="points" v-model="item.point_id" item-text="name" item-value="id" solo @change="changePoint($event, item.id)">
                                    </v-select>
                                </template>
                                <template v-slot:item.controller_id="{ item }">
                                    <v-select :items="controllers" v-model="item.controller_id" item-text="name" item-value="id" solo @change="changeContoller($event, item.id)">
                                    </v-select>
                                </template>
                                <template v-slot:item.area_id="{ item }">
                                    <v-select :items="areas" v-model="item.area_id" item-text="name" item-value="id" solo>
                                    </v-select>
                                </template>


                                <template v-slot:item.visibility="{ item }">
                                    <v-simple-checkbox
                                    v-model="item.visibility"
                                    ></v-simple-checkbox>
                                </template>

                            </v-data-table>
                        </v-tab-item>
                    </v-tabs-items>


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

        for (let sensor of sensors_raw) {
            if (sensor.visibility == 1) sensor.visibility = true;
            else sensor.visibility = false;
            sensor.logs = JSON.parse(sensor.logs.logs);
        }

        const main_vm = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: {
                drawer: true,
                mainMenu: mainMenu,
                sensors: [...sensors_raw ],
                singleExpand: true,
                active_sensors: [],
                hidden_sensors: [],
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
                    { text: 'DEOS Point', value: 'point_id' },
                    { text: 'DEOS Controller', value: 'controller_id' },
                    { text: 'Area', value: 'area_id' },
                    { text: 'Visible', value: 'visibility' },
                ],
                search: '',

            },
            ready: function() {

            },
            mounted: function() {
                //Remove All weather_forcast points from dashboard.
                index = this.points.length - 1;
                while (index >= 0) {
                    if (this.points[index].meta_type == 'weather_forcast') {
                        this.points.splice(index, 1);
                    }
                    index -= 1;
                }
                this.active_sensors = this.sensors.filter( item => item.visibility === true);
                this.hidden_sensors = this.sensors.filter( item => item.visibility === false);

                this.update_oldData();
            },
            watch: {
            },
            methods: {

                changeContoller: function (controller_id, sensor_id) {

                },
                changePoint: function(point_id, sensor_id) {

                    for (let sensor of this.sensors) {
                        if (sensor.id != sensor_id && sensor.point_id == point_id) {
                            sensor.point_id = null;
                            sensor.controller_id = null;
                            sensor.area_id = null;
                        }
                        if (sensor.id == sensor_id) {
                            for (let point of this.points) {
                                if (point.id == sensor.point_id) {
                                    sensor.controller_id = point.controller_id;
                                    sensor.area_id = point.area_id;
                                }
                            }
                        }
                    }

                },

                sendDatatoAssemblin: function(){
                    let submitdata = [];
                    for (let sensor of this.sensors)
                    {
                        if (sensor.point_id)
                        {
                            point = this.points.find(point => point.id == sensor.point_id);
                            submitdata.push({
                                "id": point.name,
                                "value": String(sensor.value)
                            });
                        }
                    }

                    var settings = {
                        "url": base_url + "/point/writePointsbyid",
                        "method": "POST",
                        "timeout": 0,
                        "headers": {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": token,
                        },
                        "data": JSON.stringify(submitdata),
                    };

                    // console.log(this.send_data_url);
                    // console.log(submitdata);

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
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            main_vm.is_relation_updating = false;
                            toastr.error('Something went wrong');
                            console.log(jqXHR);
                            console.log(textStatus);
                            console.log(errorThrown);
                    });

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
                                "visibility" : sensor.visibility
                        });
                    }
                    this.active_sensors = this.sensors.filter( item => item.visibility === true);
                    this.hidden_sensors = this.sensors.filter( item => item.visibility === false);

                },
                update_All: function(){
                    this.is_relation_updating = true;
                    let submitdata = [];
                    this.sensors = [...this.active_sensors , ...this.hidden_sensors];
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
                                "visibility" : sensor.visibility
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
                            // setTimeout(main_vm.sendDatatoAssemblin, 500);
                            // main_vm.sendDatatoAssemblin();
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
                update_relations: function() {
                    let data = [];
                    let point_data = [];
                    for (device of this.devices.data) {
                        for (observation of device.latestObservations) {
                            if (observation.point_name !== null && observation.point_name !== undefined) {
                                let value = observation.manual_value ? String(observation.manual_value) : String(observation.value);
                                data.push({
                                    "id": observation.point_name,
                                    "value": value
                                });
                                point_data.push({
                                    "deviceId": device['deviceId'],
                                    "variable": observation['variable'],
                                    "point_name": observation.point_name
                                });
                                this.is_relation_updating = true;

                            }
                        }
                    }

                    this.update_DEOS_point_name(point_data);

                    if (data.length > 0) {

                        var settings = {
                            "url": base_url + "/points/writepointsbyid",
                            "method": "PUT",
                            "timeout": 0,
                            "headers": {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": token,
                            },
                            "data": JSON.stringify(data),
                        };
                        let sensor = {};
                        for (row of data) {
                            if (sensor[row['id']] === undefined) sensor[row['id']] = 1;
                            else sensor[row['id']]++;
                        }
                        for (item in sensor) {
                            if (item == "") continue;
                            if (sensor[item] > 1) {
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
                                toastr.error(item + ' is linked more than 2 sensors, Please check again.');
                                return;
                            }
                        }

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

                        }).fail(function(jqXHR, textStatus, errorThrown) {
                            main_vm.is_relation_updating = false;
                            toastr.error('Something went wrong');
                        });
                    }
                }
            },
            computed: {

            }
        });

    </script>
@endsection
