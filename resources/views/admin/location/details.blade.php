@extends('admin.layout.master')
@section('content')
    <v-main v-if="mainMenu && deleteUrl">
        <v-container>
            @if (empty($location))
                <h1>Location not found</h1>
            @else
                <template>
                    <div class="text-center">
                        <v-form :action="updateUrl" method="POST" id="update-form">
                            @csrf
                            <v-card>
                                <v-card-title class="headline grey lighten-2">
                                    Edit Location
                                </v-card-title>
                                <v-text-field v-model="currentLocation" name="name" solo required></v-text-field>
                                <v-card-text>
                                    <v-form :action="delete_buildings_url" method="POST" id="delete_buildings-form">
                                        @csrf
                                        <v-card-text>Buildings</v-card-text>
                                        <p>@{{ buildings }}</p>
                                        <v-card-text fluid v-for="building in buildings" :key="building.id">
                                            <v-checkbox v-model="buildingSelected[building.id]">
                                                <template v-slot:label>
                                                    <div>@{{ building . name }}</div>
                                                </template>
                                            </v-checkbox>
                                        </v-card-text>
                                        <input type="hidden" name="buildingSelected" :value="JSON.stringify(buildingSelected)">
                                        <v-btn color="red" type="submit" form="delete_buildings-form">Delete Selected Buildings</v-btn>
                                    </v-form>
                                </v-card-text>
                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-form">Update</v-btn>
                                    <v-btn color="red" @click="openDelete = true">Remove</v-btn>
                                </v-card-actions>
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
            @endif
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
                this.currentLocation = this.location.name;
                this.updateUrl = `${prefix_link}/location/update/${this.location.id}`;
                this.deleteUrl = `${prefix_link}/location/delete/${this.location.id}`;
                this.delete_buildings_url = `${prefix_link}/location/delete_buildings/${this.location.id}`;
            },
            methods: {}
        })

    </script>
@endsection
