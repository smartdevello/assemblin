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
            <template>
                <div class="text-center">
                    <v-form :action="updateUrl" method="POST" id="update-form">
                        @csrf
                        <v-card class="mx-auto my-12">
                            <v-card-title class="headline grey lighten-2">
                                Edit Point
                            </v-card-title>
                            <v-card-text></v-card-text>
                            <v-card-text>
                                <v-text-field v-model="point.label" name="label" label = "DEOS page and sensor" :rules="[ v => !!v || 'Field is required', ]" required></v-text-field>
                                <v-text-field v-model="point.name" name="name" label="Name" :rules="[ v => !!v || 'Field is required', ]" required></v-text-field>
                                <div>
                                    <v-select :items="controllers" label="Select a Controller" name="controller_id" v-model="point.controller_id" item-text="name" item-value="id" solo required>
                                </div>
                                <div>
                                    <v-select :items="areas" label="Select an Area" name="area_id" v-model="point.area_id" item-text="name" item-value="id" solo required>
                                </div>
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
                controllers: ( <?php echo json_encode($controllers); ?> ),
                areas: ( <?php echo json_encode($areas); ?> ),
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
