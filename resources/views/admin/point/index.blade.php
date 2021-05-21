@extends('admin.layout.master')
@section('content')
    <v-main >
        <v-container>
            @if( \Session::has('success') ) 
                <h3>{{ \Session::get('success') }}</h3>
            @else
                @if( count($errors) > 0)
                    @foreach($errors->all() as $error)
                        <h3 style="color: red">{{ $error }}</h3>
                    @endforeach
                @endif
            @endif

            <v-row>
                <v-card v-for="point in points" :key="point.id"   @click="openUpdateModal(point.id)" width="300" elevation="10" class="ma-2">
                    <v-card-title>@{{ point . label }}</v-card-title>
                    <v-card-title>@{{ point . name }}</v-card-title>
                    <v-row>
                        <v-col cols="6">
                            <v-card-subtitle >
                                @{{point.controller ? point.controller.name : ""}}
                            </v-card-subtitle>
                        </v-col>
                        <v-col cols="6">
                            <v-card-subtitle >
                                @{{point.area ? point.area.name : ""}}
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
                                        Add new Point
                                    </v-card-title>
                                    <v-text-field v-model="currentPointLabel" name="label" label="DEOS page and sensor" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
                                    <v-text-field v-model="currentPointName" name="name" label="Point Name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>

                                    <v-select :items="controllers" label="Select A Controller" name="controller_id" item-text="name" item-value="id" solo required >
                                    </v-select>

                                    <v-select :items="areas" label="Select an Area" name="area_id" item-text="name" item-value="id" solo required >
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
                points: ( <?php echo json_encode($points); ?> ),
                controllers: ( <?php echo json_encode($controllers); ?> ),
                areas: ( <?php echo json_encode($areas); ?> ),
                createUrl: `${prefix_link}/point/create`,
                openNew: false,
                currentPointName: '',
                currentPointLabel: ''
            },
            mounted: function() {

            },            
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/point/${id}`;
                }
            }
        });
    </script>
@endsection
