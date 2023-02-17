@extends('admin.layout.master')
@section('content')
    <v-main v-if="mainMenu && deleteUrl">
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
                                    Edit Area
                                </v-card-title>
                                <v-card-text>
                                    <v-text-field v-model="currentArea" label="Area Name" name="name" required></v-text-field>
                                    <v-select :items="buildings" label="Select a Building" name="building_id" v-model="currentBuilding" item-text="name" item-value="id" solo required>
                                </v-card-text>

                                <v-card-actions>
                                    <v-btn color="primary" text type="submit" form="update-form">Update</v-btn>
                                    <v-btn color="red" @click="openDelete = true">Remove</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-form>
                        <v-form>
                            <v-card class="mx-auto my-12" v-if="area.points.length > 0">
                                <v-card-title>DEOS Points</v-card-title>
                                <v-card-text fluid v-for="item in area.points" :key="item.id">
                                        <div class="mx-3">@{{ item.label }}</div>
                                        <div class="mx-3">@{{ item.name }}</div>
                                        <div class="mx-3">@{{ item.value }}</div>
                                </v-card-text>
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
                buildings:( <?php echo json_encode($buildings); ?> ),
                area: ( <?php echo json_encode($area); ?> ),
                currentArea: "",
                currentBuilding: 0,
                updateUrl: "",
                deleteUrl: "",
                openDelete: false
            },
            mounted: function() {
                this.currentArea = this.area.name;
                this.currentBuilding = this.area.building_id;

                this.updateUrl = `${prefix_link}/area/update/${this.area.id}`;
                this.deleteUrl = `${prefix_link}/area/delete/${this.area.id}`;
            }
        })

    </script>
@endsection
