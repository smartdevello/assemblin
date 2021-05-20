@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!areas">
        <v-container>
            <v-row>
                <v-card v-for="area in areas" :key="area.id" @click="openUpdateModal(area.id)" width="300" elevation="10" class="ma-2">
                    <v-card-title>@{{ area . name }}</v-card-title>
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
                                        Add New Area
                                    </v-card-title>
                                    <v-text-field v-model="currentArea" name="name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
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
                areas: ( <?php echo json_encode($areas); ?> ),
                buildings: ( <?php echo json_encode($buildings); ?> ),
                openNew: false,
                currentArea: "",
                createUrl: `${prefix_link}/area/create`,
                currentUrl: '',
                selectedBuilding: ''
            },
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/area/${id}`;
                }
            }
        })

    </script>
@endsection
