@extends('admin.layout.master')
@section('content')
    <v-main v-if="mainMenu && deleteUrl">
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
                        <v-form :action="updateUrl" method="POST" id="update-form" enctype="multipart/form-data">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title class="headline grey lighten-2">
                                    Edit Location
                                </v-card-title>

                                <v-img
                                    :src="location.img_url"
                                    contain
                                    max-height="300"
                                    max-width="500"
                                >
                                    <v-file-input
                                        :rules = "image_rules"
                                        accept="image/png, image/jpeg, image/bmp"
                                        hide-input
                                        truncate-length="50"
                                        name="image"
                                        prepend-icon="mdi-camera"
                                        v-model="image"
                                        @change = "Preview_image"
                                    ></v-file-input>
                                </v-img>



                                <v-text-field v-model="location.name" name="name" solo required></v-text-field>

                                <v-checkbox
                                        v-model="location.enable_kiona_endpoint" label="Enable Kiona Endpoint (/api/kiona/@{{ location.name }})" name="enable_kiona_endpoint"
                                        :value="location.enable_kiona_endpoint"
                                        >
                                </v-checkbox>
                                <v-row justify="left">
                                    <v-card-text>
                                        
                                    </v-card-text>
                                </v-row>
                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-form">Update</v-btn>
                                    <v-btn color="red" @click="openDelete = true">Remove</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                        <v-form :action="delete_buildings_url" method="POST" id="delete_buildings-form">
                            @csrf
                            <v-card v-if="buildings.length > 0" class="mx-auto my-12 pb-3">
                                <v-card-text>Buildings</v-card-text>
                                <v-card-text fluid v-for="building in buildings" :key="building.id">
                                    <v-checkbox v-model="buildingSelected[building.id]">
                                        <template v-slot:label>
                                            <div>@{{ building . name }}</div>
                                        </template>
                                    </v-checkbox>
                                </v-card-text>
                                <input type="hidden" name="buildingSelected" :value="JSON.stringify(buildingSelected)">
                                <v-btn color="red" type="submit" form="delete_buildings-form">Delete Selected Buildings</v-btn>
                            </v-card>
                        </v-form>
                    </div>
                </template>
                <v-dialog v-model="openDelete" width="500" v-if="!!deleteUrl">
                    <v-form :action="deleteUrl" method="POST" id="create-form">
                        @csrf
                        <v-card>
                            <v-card-title class="headline grey lighten-2">Are you sure to delete this item?</v-card-title>
                            <v-divider></v-divider>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red" text type="submit" form="create-form">Confirm</v-btn>
                                <v-btn text @click="openDelete = false">Cancel</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-dialog>

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
                image_rules: [
                    value => !value || value.size > 2000000 || 'Image size should be greater than 2 MB!',
                ],
                image: null,
                location: ( <?php echo json_encode($location); ?> ),
                buildings: ( <?php echo json_encode($buildings); ?> ),
                currentLocation: "",
                updateUrl: "",
                deleteUrl: "",
                delete_buildings_url: "",
                openDelete: false,
                buildingSelected: {}
            },
            mounted: function() {
                console.log(this.location);
                this.currentLocation = this.location.name;

                if (! this.location.img_url ) {
                    this.location.img_url = "https://www.gravatar.com/avatar/HASH";
                }
                
                this.updateUrl = `${prefix_link}/location/update/${this.location.id}`;
                this.deleteUrl = `${prefix_link}/location/delete/${this.location.id}`;
                this.delete_buildings_url = `${prefix_link}/location/delete_buildings/${this.location.id}`;
            },
            methods: {
                Preview_image: function(){
                    this.location.img_url = URL.createObjectURL(this.image);
                }
            }
        })

    </script>
@endsection
