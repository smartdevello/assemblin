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

    </style>
@endsection
@section('content')
    <v-main>
        <v-container>
            <v-row>
                <v-col cols="12" sm="8" md="8">
                    <div v-if="devices" class="section_container sensors">
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
                            <v-col cols="12" sm="1" md="1">
                                Latest value
                            </v-col>
                            <v-col cols="12" sm="1" md="1">
                                Manual Value
                            </v-col>
                        </v-row>
                        <div v-for="device in devices.data">
                            <div v-for="observation in device.latestObservations">
                                <v-row>
                                    <v-col cols="12" sm="2" md="2">
                                        <v-text-field v-model="device.deviceId" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2" md="2">
                                        <v-text-field v-model="observation.id" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2" md="2">
                                        <v-text-field v-model="device.tags[0]" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2" md="2">
                                        <v-text-field v-model="device.displayName" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="2" md="2">
                                        <v-text-field v-model="observation.variable" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="1" md="1">
                                        <v-text-field v-model="observation.value" solo></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="1" md="1">
                                        <v-text-field v-model="observation.manual_value" solo></v-text-field>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="3" md="3">
                    <div v-if="DEOSPoints" class="section_container DEOS">
                        <h1 class="section_title">DEOS</h1>
                        <v-row>
                            <v-col cols="12" sm="8" md="8">
                                DEOS point name
                            </v-col>
                            <v-col cols="12" sm="4" md="4">
                                DEOS Controller
                            </v-col>
                        </v-row>
                        <div v-for="device in devices.data">
                            <div v-for="observation in device.latestObservations">
                                <v-row>
                                    <v-col cols="12" sm="7" md="7">
                                        <v-select :items="DEOSPoints" v-model="observation.DEOS_pointId" item-text="id" item-value="id" solo>
                                        </v-select>
                                    </v-col>
                                    <v-col cols="12" sm="5" md="5">
                                        <v-select v-if="asm_serverconfig" :items="asm_serverconfig.Slaves" item-text="Name" item-value="Name" solo></v-select>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </div>
                </v-col>
                <v-col cols="12" sm="1" md="1">
                    <div v-if="devices" class="section_container Areas">
                        <h1 class="section_title">Areas</h1>
                        <v-row>
                            <v-col cols="12" sm="6" md="6">
                                Area name
                            </v-col>
                        </v-row>
                        <div v-for="device in devices.data">
                            <div v-for="observation in device.latestObservations">
                                <v-row>
                                    <v-col cols="12" sm="12" md="12">
                                        <v-text-field solo></v-text-field>
                                    </v-col>
                                </v-row>
                            </div>
                        </div>
                    </div>
                </v-col>
            </v-row>
            <v-row v-if="devices">
                <v-col cols="12" sm="10" md="10">

                </v-col>
                <v-col cols="12" sm="2" md="2">
                    <v-btn :loading="is_relation_updating" :disabled="is_relation_updating" outlined @click="update_relations">Update</v-btn>
                </v-col>
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
                devices: null,
                DEOSPoints: null,
                asm_serverconfig: null,
                asm_restconfig: {},
                is_relation_updating: false
            },

            mounted: function() {
                this.getFoxeriotDevices();
                this.getDEOSPoints();
                this.getAsmServerConfig();
            },
            watch: {
                devices: function() {

                }
            },
            methods: {
                getFoxeriotDevices: function() {
                    return $.ajax({
                        url: base_url + "/api/foxeriot/devices",
                        success: function(data) {
                            main_vm.devices = JSON.parse(data);
                            for (device of main_vm.devices.data) {
                                // console.log(device);
                                for (observation of device.latestObservations) {
                                    let res = main_vm.getDEOS_pointId(device['deviceId'], observation['variable']);
                                    if (res.status == 200) {
                                        let resdata = JSON.parse(res.responseText);
                                        observation.DEOS_pointId = resdata.DEOS_pointId ? resdata.DEOS_pointId : "";
                                    }

                                }
                            }

                        },
                        error: function(err) {

                        }
                    });
                },
                getDEOS_pointId: function(deviceId, variable) {
                    return $.ajax({
                        url: base_url + "/api/foxeriot/getDEOS_pointId",
                        data: {
                            "deviceId": deviceId,
                            "variable": variable
                        },
                        success: function(data) {
                            // console.log(data);
                        },
                        error: function(err) {

                        }
                    });
                },
                getAsmServerConfig: function() {
                    $.ajax({
                        url: base_url + "/api/asm_server/config/getSERVERConfig",
                        success: function(data) {
                            console.log(data);
                            main_vm.asm_serverconfig = JSON.parse(data);
                            console.log(main_vm.asm_serverconfig);
                            for (let slave of main_vm.asm_serverconfig["Slaves"]) {
                                main_vm.getAsmRestConfig(slave);
                            }
                        },
                        error: function(err) {
                            console.error(err);
                        }
                    });
                },
                getAsmRestConfig: function(slave) {
                    let ip = slave['IP'];
                    let name = slave['Name'];
                    let Port = slave['Port'];

                    $.ajax({
                        url: base_url + "/api/asm_server/config/getRESTconfig?name=" + name,
                        success: function(data) {
                            main_vm.asm_restconfig[name] = JSON.parse(JSON.parse(data));
                            // console.log(main_vm.asm_restconfig[name]);
                        },
                        error: function(err) {
                            console.error(err);
                        }
                    });

                },
                getDEOSPoints: function() {

                    return $.ajax({
                        url: base_url + "/api/points",
                        success: function(data) {
                            main_vm.DEOSPoints = JSON.parse(data);
                            main_vm.DEOSPoints.push({
                                "id": "",
                                "value": ""
                            });
                        },
                        error: function(err) {
                            console.log(err);
                        }
                    });
                },
                update_DEOS_pointId: function(point_data) {
                    console.log(point_data);
                    return $.ajax({
                        type: "PUT",
                        url: base_url + "/api/foxeriot/devices",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        data: JSON.stringify(point_data),
                        success: function(data) {

                        },
                        error: function(xhr, status, error) {}
                    });
                },
                update_relations: function() {
                    let data = [];
                    let point_data = [];
                    for (device of this.devices.data) {
                        for (observation of device.latestObservations) {
                            if (observation.DEOS_pointId !== null && observation.DEOS_pointId !== undefined) {
                                let value = observation.manual_value ? String(observation.manual_value) : String(observation.value);
                                data.push({
                                    "id": observation.DEOS_pointId,
                                    "value": value
                                });
                                point_data.push({
                                    "deviceId": device['deviceId'],
                                    "variable": observation['variable'],
                                    "DEOS_pointId": observation.DEOS_pointId
                                });
                                this.is_relation_updating = true;

                            }
                        }
                    }

                    this.update_DEOS_pointId(point_data);

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
                // unique_url: function() {
                //     return "https://kiinde.com/promo/" + this.coupon.code + "/a";
                // }
            }
        });

    </script>
@endsection
