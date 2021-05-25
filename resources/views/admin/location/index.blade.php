@extends('admin.layout.master')
@section('content')
    <v-main v-if="!!locations">
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
            <v-row>
                <v-card v-for="location in locations" :key="location.id" @click="openUpdateModal(location.id)" width="300" elevation="10" class="ma-2">
                    <v-img
                        :src="location.img_url"
                        contain
                        max-height="150"
                        max-width="150"
                    ></v-img>

                    <v-card-title>@{{ location . name }}</v-card-title>
                    <v-card-subtitle v-for="building in location.buildings" :key="building.id">
                        @{{building.name}}
                    </v-card-subtitle>
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
                                        Add new location
                                    </v-card-title>
                                    <v-text-field v-model="currentLocation" name="name" required class="pa-2"></v-text-field>

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
                locations: ( <?php echo json_encode($locations); ?> ),
                openNew: false,
                currentLocation: "",
                createUrl: `${prefix_link}/location/create`,
                currentUrl: '',
            },
            mounted: function() {
                for (let location of this.locations) {
                    if (!location.img_url ) location.img_url = "https://www.gravatar.com/avatar/HASH";                        
                }
            },            
            methods: {
                openUpdateModal: function(id) {
                    window.location.href = `${prefix_link}/location/${id}`;
                }
            }
        })

    </script>
@endsection
