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
                                {{-- <v-card-text>
                                    <v-select :items="types" label="Select a type" name="type" v-model="currentType" item-text="type" item-value="type" solo required>
                                </v-card-text> --}}
                                <v-card-text>
                                    <v-select :items="intervals" label="Select an interval" name="interval" v-model="currentInterval" item-text="text" item-value="value" solo required>
                                </v-card-text>

                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-Interval_form">Update</v-btn>
                                    {{-- <v-btn color="red" @click="openDelete = true">Remove</v-btn> --}}
                                </v-card-actions>
                            </v-card>
                        </v-form>
                    </div>
                </template>

                <v-row>
                    <template>
                        <div class="text-center">
                            <v-dialog v-model="openNewTokenForm" width="500">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on" class="ma-3">Add</v-btn>
                                </template>
    
                                <v-form :action="createTokenUrl" method="POST" id="create_token_form">
                                    @csrf
                                    <v-card>
                                        <v-card-title class="headline grey lighten-2">
                                            Add New API Token
                                        </v-card-title>
                                        <v-text-field name="token_name" required class="pa-2"></v-text-field>
    
                                        <v-card-actions>
                                            <v-spacer></v-spacer>
                                            <v-btn color="primary" text type="submit" form="create_token_form">Submit</v-btn>
                                        </v-card-actions>
                                    </v-card>
                                </v-form>
                            </v-dialog>
                        </div>
                    </template>
                </v-row>

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
            createTokenUrl: "",
            currentDevice : "",
            currentType: "",
            currentInterval: 0,
            openNewTokenForm: false,
            devices: ( <?php echo json_encode($devices); ?> ),
            alltypes:  ( <?php echo json_encode($types); ?> ),
            all_tokens: ( <?php echo json_encode($all_tokens); ?> ),
            types: [],
            intervals: [
                {
                    text: "10 mins",
                    value: '2'
                },
                {
                    text: "15 mins",
                    value: '3'
                },
                {
                    text: "30 mins",
                    value: '6'
                },
                {
                    text: "60 mins",
                    value: '12'
                },
                {
                    text: "120 mins",
                    value: '24'
                },
                {
                    text: "360 mins",
                    value: '72'
                }
            ]
        },
        mounted: function() {
            this.updateIntervalUrl = `${prefix_link}/setting/update_device_interval`;
            this.createTokenUrl = `${prefix_link}/tokens/create`;
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