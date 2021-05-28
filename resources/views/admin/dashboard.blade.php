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
                    {{-- item-key="observationId" --}}
                    {{-- show-group-by
                    group-by="deviceId" --}}
                    multi-sort
                    :footer-props="{
                        showFirstLastPage: true,
                        firstIcon: 'mdi-arrow-collapse-left',
                        lastIcon: 'mdi-arrow-collapse-right',
                        prevIcon: 'mdi-minus',
                        nextIcon: 'mdi-plus'
                      }"
                  >

                    <template v-slot:item.value="{ item }">

                        <v-text-field v-model="item.value" solo></v-text-field>
                        {{-- <v-chip
                        :color="getColor(item.value)"
                        dark
                        >
                        @{{ item.value }}
                        </v-chip> --}}
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

                </v-data-table>
                </v-card>
              </template>


            {{-- <v-row>
                <v-col cols="12" sm="8" md="8">
                    <div class="section_container sensors">
                        <h1 class="section_title">Sensors</h1>
                        <v-row>
                            <v-col cols="12" sm="2" md="2">
                                Device ID
                            </v-col>
                            <v-col cols="12" sm="2" md="2">
                                Sensor ID
                            </v-col>
                            <v-col cols="12" sm="2" md="2">
                                Tag
                            </v-col>
                            <v-col cols="12" sm="2" md="2">
                                Name
                            </v-col>
                            <v-col cols="12" sm="2" md="2">
                                Type
                            </v-col>
                            <v-col cols="12" sm="2" md="2">
                                Latest value
                            </v-col>
                        </v-row>
                        <div v-for="sensor in sensors.data">
                            <v-row>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.deviceId" solo></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.observationId" solo></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.tag" solo></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.name" solo></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.type" solo></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="2" md="2">
                                    <v-text-field v-model="sensor.value" solo></v-text-field>
                                </v-col>

                            </v-row>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="3" md="3">
                    <div class="section_container DEOS">
                        <h1 class="section_title">DEOS</h1>
                        <v-row>
                            <v-col cols="12" sm="6" md="6">
                                DEOS point name
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                DEOS Controller
                            </v-col>
                        </v-row>
                        <div v-for="sensor in sensors.data">
                                <v-row>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-select :items="points" v-model="sensor.point_id" item-text="name" item-value="id" solo @change="changePoint($event, sensor.id)">
                                        </v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-select :items="controllers" v-model="sensor.controller_id" item-text="name" item-value="id" solo @change="changeContoller($event, sensor.id)">
                                        </v-select>
                                    </v-col>
                                </v-row>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="1" md="1">
                    <div class="section_container Areas">
                        <h1 class="section_title">Areas</h1>
                        <v-row>
                            <v-col cols="12" sm="12" md="12">
                                Area name
                            </v-col>
                        </v-row>
                        <div v-for="sensor in sensors.data">
                                <v-row>
                                    <v-col cols="12" sm="12" md="12">
                                        <v-select :items="areas" v-model="sensor.area_id" item-text="name" item-value="id" solo>                                            
                                        </v-select>
                                    </v-col>
                                </v-row>
                        </div>
                    </div>
                </v-col>
            </v-row>
            <template>
                <div class="text-center">
                  <v-pagination
                    v-model="page"
                    :length="sensors.last_page"
                  ></v-pagination>
                </div>
            </template> --}}

            <v-row >
                <v-col cols="12" sm="10" md="10">

                </v-col>
                <v-col cols="12" sm="2" md="2">
                    <v-btn class="blue text-center white--text mt-10" :loading="is_relation_updating" :disabled="is_relation_updating" outlined @click="update_All">Update</v-btn>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
@endsection

@section('script')
    <script>

        var sensors_raw = <?php echo json_encode($sensors); ?>;
        const main_vm = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: {
                drawer: true,
                mainMenu: mainMenu,
                sensors: sensors_raw,
                page: sensors_raw.current_page,
                points: ( <?php echo json_encode($points); ?> ),
                controllers: ( <?php echo json_encode($controllers); ?> ),
                areas: ( <?php echo json_encode($areas); ?> ),
                is_relation_updating: false,
                update_dashboard_url : `${prefix_link}/api/dashboard/update`,
                send_data_url : `${base_url}/api/point/writePointsbyid`,



                headers: [
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
                ],              
                search: '',

            },

            mounted: function() {
                // console.log(sensors_raw);
                this.page = this.sensors.current_page;
                console.log(this.sensors);
            },
            watch: {
                page: function() {
                    console.log('current_page is ' + this.page);
                    window.location.href = "/?page=" + this.page;
                }
            },
            methods: {
                getColor (calories) {
                    if (calories > 400) return 'red'
                    else if (calories > 200) return 'orange'
                    else return 'green'
                },
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
                            "url": this.send_data_url,
                            "method": "POST",
                            "timeout": 0,
                            "headers": {
                                "Content-Type": "application/json"
                            },
                            "data": JSON.stringify(submitdata),
                    };

                    $.ajax(settings).done(function(response) {
                            console.log(response);
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR);
                    });
                    
                },

                update_All: function(){
                    this.is_relation_updating = true;
                    let submitdata = [];
                    for (let sensor of this.sensors)
                    {
                        submitdata.push({
                            "id" : sensor.id,
                            "value" : sensor.value,
                            "point_id" : sensor.point_id,
                            "controller_id" : sensor.controller_id,
                            "area_id" : sensor.area_id
                        });
                    }

                    var settings = {
                            "url": this.update_dashboard_url,
                            "method": "POST",
                            "timeout": 0,
                            "headers": {
                                "Content-Type": "application/json"
                            },
                            "data": JSON.stringify(submitdata),
                    };
                    
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
                            main_vm.sendDatatoAssemblin();
                            toastr.success('Updated Successfully');
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
                            "url": base_url + "/api/points/writepointsbyid",
                            "method": "PUT",
                            "timeout": 0,
                            "headers": {
                                "Content-Type": "application/json"
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
