@extends('admin.layout.master')
@section('content')
    <v-main >
        <v-container>
            @if (empty($point))
                <h1>Point not found</h1>
            @else
                <template>
                    <div class="text-center">
                        <v-form :action="updateUrl" method="POST" id="update-form">
                            @csrf
                            <v-card class="mx-auto my-12">
                                <v-card-title class="headline grey lighten-2">
                                    Edit Point
                                </v-card-title>
                                <v-text-field v-model="point.label" name="label" solo required></v-text-field>
                                <v-text-field v-model="point.name" name="name" solo required></v-text-field>

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
                point: ( <?php echo json_encode($point); ?> ),
                updateUrl: "",
                deleteUrl: "",
                openDelete: false,
            },
            mounted: function() {

                this.updateUrl = `${prefix_link}/point/update/${this.point.id}`;
                this.deleteUrl = `${prefix_link}/point/delete/${this.point.id}`;
            },
            methods: {}
        })

    </script>
@endsection
