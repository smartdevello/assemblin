@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!buildings">
        <v-container>
            <v-row>
                <v-card v-for="building in buildings" :key="building.id" @click="openUpdateModal(building.id)" width="300" elevation="10" class="ma-2">


                    <v-img
                        :src="building.img_url"
                        contain
                        max-height="150"
                        max-width="150"
                    ></v-img>
                    <v-card-title>@{{ building . name }}</v-card-title>
                    <v-row>
                        <v-col cols="6">
                            <v-card-subtitle v-for="area in building.areas" :key="area.id">
                                @{{ area . name }}
                            </v-card-subtitle>
                        </v-col>
                        <v-col cols="6">
                            <v-card-subtitle v-for="item in building.controllers" :key="item.id">
                                @{{ item . name }}
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
                                        Add New Building
                                    </v-card-title>
                                    <v-text-field v-model="currentBuilding" name="name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
                                    <v-select :items="locations" label="Select A Location" name="location_id" item-text="name" item-value="id" solo required>
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
                buildings: ( <?php echo json_encode($buildings); ?> ),
                locations: ( <?php echo json_encode($locations); ?> ),
                openNew: false,
                currentBuilding: "",
                createUrl: `${prefix_link}/building/create`,
                currentUrl: '',
            },
            mounted: function(){
                for (let building of this.buildings) {
                    if (! building.img_url ){
                        building.img_url = "https://www.gravatar.com/avatar/HASH";
                    }
                }
            },
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/building/${id}`;
                }
            }
        })

    </script>
@endsection
