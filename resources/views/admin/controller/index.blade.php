@extends('admin.layout.master')
@section('content')
    <v-main >
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
            <v-row>
                <v-card v-for="controller in controllers" :key="controller.id" @click="openUpdateModal(controller.id)" width="300" elevation="10" class="ma-2">
                    <v-row>
                        <v-col cols = "6"> <v-card-title>@{{ controller . name }}</v-card-title> </v-col>
                        <v-col cols = "6"> <v-card-title>@{{ controller . building . name }}</v-card-title> </v-col>
                    </v-row>
                    
                    <v-row>
                        <v-col cols="6">
                            <v-card-subtitle v-for="point in controller.points" :key="point.id">
                                @{{ point . name }}
                            </v-card-subtitle>
                        </v-col>
                    </v-row>

                </v-card>
            </v-row>
            <v-row>
                <template>
                    <div class="text-center">
                        <v-dialog v-model="openNew" width="500">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on" class="ma-3">Add</v-btn>
                            </template>

                            <v-form :action="createUrl" method="POST" id="create-form">
                                @csrf
                                <v-card>
                                    <v-card-title class="headline grey lighten-2">
                                        Add New Controller
                                    </v-card-title>

                                    <v-text-field placeholder="Name" name="name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
                                    <v-text-field placeholder="IP Address" name="ip_address" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
                                    <v-text-field placeholder="Port Number" name="port_number" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]" readonly :value="nextId + 8000"></v-text-field>
                                    <v-select :items="buildings" label="Select A Building" name="building_id" item-text="name" item-value="id" solo required>                                        
                                    </v-select>

                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="primary" text type="submit" form="create-form">Submit</v-btn>
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

@section('script')
    <script>
        const main_vm = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: {
                drawer: true,
                mainMenu: mainMenu,
                controllers: ( <?php echo json_encode($controllers); ?> ),
                buildings: ( <?php echo json_encode($buildings); ?> ),
                nextId: ( <?php echo json_encode($nextId); ?> ),
                openNew: false,
                createUrl: `${prefix_link}/controller/create`,
                currentUrl: '',
            },
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/controller/${id}`;
                }
            },
            mounted: function(){
                for (let controller of this.controllers) {
                    if (!controller.building) {
                        controller.building = {};
                    }
                    controller.points.forEach( (item, index, object) => {
                        if (item.meta_type == "weather_forcast") {
                            object.splice(index, 1);
                        }
                    });
                }

            }


        })

    </script>
@endsection
