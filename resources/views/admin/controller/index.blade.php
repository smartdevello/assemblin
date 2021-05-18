@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!controllers">
        <v-container>
            <v-row>
                <v-card v-for="controller in controllers" :key="controller.id" @click="openUpdateModal(controller.id)" width="300" elevation="10" class="ma-2">
                    <v-card-title>@{{ controller.name }}</v-card-title>
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
                                    <v-text-field v-model="currentController" name="name" required class="pa-2"></v-text-field>
                                    <v-select :items="areas" label="Select an Area"  name="area_id" v-model="selectedArea" item-text="name" item-value="id" solo required>
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
                controllers: ( <?php echo json_encode($controllers); ?> ),
                openNew: false,
                currentController: "",
                createUrl: `${prefix_link}/controller/create`,
                currentUrl: '',
            },
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/controller/${id}`;
                }
            }
        })

    </script>
@endsection
