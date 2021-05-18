@extends('admin.layout.master')
@section('content')
    <v-main v-if="mainMenu && deleteUrl">
        <v-container>
            @if (empty($building))
                <h1>Building not found</h1>
            @else
                <template>
                    <div class="text-center">
                        <v-form :action="updateUrl" method="POST" id="update-form">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title class="headline grey lighten-2">
                                    Edit Building
                                </v-card-title>
                                <v-card-text>
                                    <v-text-field v-model="currentBuilding" label="Building Name" name="name" required></v-text-field>                                
                                    <v-select :items="locations" label="Select a Location" name="location_id" v-model="currentLocation" item-text="name" item-value="id" solo required>                                
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
                locations:( <?php echo json_encode($locations); ?> ),
                building: ( <?php echo json_encode($building); ?> ),
                currentBuilding: "",
                currentLocation: 0,
                updateUrl: "",
                deleteUrl: "",
                openDelete: false
            },
            mounted: function() {
                this.currentBuilding = this.building.name;
                this.currentLocation = this.building.location_id;
                this.updateUrl = `${prefix_link}/building/update/${this.building.id}`;
                this.deleteUrl = `${prefix_link}/building/delete/${this.building.id}`;
            }
        })

    </script>
@endsection
