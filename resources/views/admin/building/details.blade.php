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
                            <v-card>
                                <v-card-title class="headline grey lighten-2">
                                    Edit Building
                                </v-card-title>
                                <v-text-field v-model="currentBuilding" name="name" solo required></v-text-field>

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
                building: ( <?php echo json_encode($building); ?> ),
                currentBuilding: "",
                updateUrl: "",
                deleteUrl: "",
                openDelete: false
            },
            mounted: function() {
                this.currentBuilding = this.building.name;
                this.updateUrl = `${prefix_link}/building/update/${this.building.id}`;
                this.deleteUrl = `${prefix_link}/building/delete/${this.building.id}`;
            }
        })

    </script>
@endsection
