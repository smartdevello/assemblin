@extends('admin.layout.master');
@section('content');
    <v-main>
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
                    <div class="text-center">
                        <v-form :action="updateIntervalUrl" method="POST" id="update-Interval_form">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title class="headline grey lighten-2">
                                    Update Device Interval
                                </v-card-title>
                                <v-card-text>
                                    <v-select :items="devices" label="Select a Device" name="deviceId" v-model="currentDevice" item-text="deviceId" item-value="deviceId" solo required @change="changeDevice($event)" >
                                </v-card-text>
                                <v-card-text>
                                    <v-select :items="types" label="Select a type" name="type" v-model="currentType" item-text="type" item-value="type" solo required>
                                </v-card-text>
                                <v-card-text>
                                    {{-- <v-text-field v-model="controller.name" label="Controller Name" name="name" required></v-text-field>
                                    <v-text-field v-model="controller.ip_address" label="IP Address" name="ip_address" required></v-text-field>
                                    <v-text-field v-model="controller.port_number" label="Port Number" name="port_number" required readonly></v-text-field>
                                    <v-select :items="buildings" label="Select a Building" name="building_id" v-model="currentBuilding" item-text="name" item-value="id" solo required> --}}
                                </v-card-text>

                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-Interval_form">Update</v-btn>
                                    {{-- <v-btn color="red" @click="openDelete = true">Remove</v-btn> --}}
                                </v-card-actions>
                            </v-card>
                        </v-form>
                    </div>
                </template>
        </v-container>
    </v-main>
@endsection

@section('script');
<script>
    const main_vm = new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        data: {
            drawer: true,
            mainMenu: mainMenu,
            updateIntervalUrl: "",
            currentDevice : "",
            devices: ( <?php echo json_encode($devices); ?> ),
            alltypes:  ( <?php echo json_encode($types); ?> ),
            types: [],
        },
        mounted: function() {
            this.updateIntervalUrl = `${prefix_link}/setting/update_device_interval`;
            console.log(this.devices);
            console.log(this.alltypes);
        },
        methods: {        
            changeDevice: function(deviceId){
                this.types = this.alltypes[deviceId];
            }
        },
    })
</script>
@endsection