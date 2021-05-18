@extends('admin.layout.master')
@section('content')
    <v-main>
        <v-container>
            @if (empty($controller))
                <h1>Controller not found</h1>
            @else
                <template>
                    <div class="text-center">
                        <v-form :action="updateUrl" method="POST" id="update-form">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title class="headline grey lighten-2">
                                    Edit Controller
                                </v-card-title>
                                <v-card-text>
                                    <v-text-field v-model="controller.name" label="Controller Name" name="name" required></v-text-field>
                                    <v-text-field v-model="controller.ip_address" label="IP Address" name="ip_address" required></v-text-field>
                                    <v-text-field v-model="controller.port_number" label="Port Number" name="port_number" required></v-text-field>
                                    <v-select :items="buildings" label="Select a Building" name="building_id" v-model="currentBuilding" item-text="name" item-value="id" solo required>
                                </v-card-text>

                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-form">Update</v-btn>
                                    <v-btn color="red" @click="openDelete = true">Remove</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                        <v-form :action="removePointsUrl" method="POST" id="remove-points-form">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title>DEOS Points</v-card-title>
                                <v-card-text fluid v-for="item in controller.points" :key="item.id">
                                    <v-checkbox v-model="pointSelected[item.id]">
                                        <template v-slot:label>
                                            <div class="mx-3">@{{ item . name }}</div>
                                            <div class="mx-3">@{{ item . sensor }}</div>
                                        </template>
                                    </v-checkbox>
                                </v-card-text>
                                <input type="hidden" name="pointSelected" :value="JSON.stringify(pointSelected)">
                                <v-card-actions>
                                    <v-btn color="red" text type="submit" form="remove-points-form">Delete Selected Points</v-btn>
                                    <v-btn color="primary" @click="openPoint = true">Add New Point</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                        <v-form :action="importPointsUrl" method="POST" id="import-points-form" enctype="multipart/form-data">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title>Import DEOS points from CSV file</v-card-title>
                                <v-file-input name="file" accept=".csv, .xlsx, application/vnd.ms-excel" label="Select File" outlined dense></v-file-input>
                                <v-card-actions>
                                    <v-btn color="primary" type="submit" form="import-points-form">Import</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                        <v-card class="mx-auto my-12">
                            <v-card-actions>
                                <v-btn color="primary" @click="exportPoints()">Export Points</v-btn>
                            </v-card-actions>
                        </v-card>
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
                <v-dialog v-model="openPoint" width="500">
                    <v-form :action="addPointUrl" method="POST" id="add-point-form">
                        @csrf
                        <v-card>
                            <v-card-title class="headline grey lighten-2">
                                Add New Point
                            </v-card-title>
                            <v-text-field name="name" placeholder="name" required class="pa-2"></v-text-field>
                            <v-text-field name="sensor" placeholder="sensor" required class="pa-2"></v-text-field>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary" text type="submit" form="add-point-form">Submit</v-btn>
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
                buildings: ( <?php echo json_encode($buildings); ?> ),
                controller: ( <?php echo json_encode($controller); ?> ),
                currentBuilding: 0,
                updateUrl: "",
                deleteUrl: "",
                openDelete: false,
                addPointUrl: "",
                removePointsUrl: "",
                importPointsUrl: "",
                openPoint: false,
                pointSelected: {}
            },
            mounted: function() {
                this.currentBuilding = this.controller.building_id;
                this.updateUrl = `${prefix_link}/controller/update/${this.controller.id}`;
                this.deleteUrl = `${prefix_link}/controller/delete/${this.controller.id}`;
                this.addPointUrl = `${prefix_link}/controller/${this.controller.id}/add-point`;
                this.removePointsUrl = `${prefix_link}/controller/${this.controller.id}/remove-points`;
                this.importPointsUrl = `${prefix_link}/controller/${this.controller.id}/import-points`;
            },
            methods: {
                exportPoints: function() {
                    window.open(`${prefix_link}/controller/${this.controller.id}/export-points`, '_blank');
                }
            }
        })

    </script>
@endsection
