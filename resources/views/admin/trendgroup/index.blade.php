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
                <v-card>
                    <v-card-title>
                        Trend Groups
                        <v-spacer></v-spacer>
                        <v-text-field
                          v-model="search"
                          append-icon="mdi-magnify"
                          label="Search"
                          single-line
                          hide-details
                        ></v-text-field>
                    </v-card-title>
                  <v-data-table
                    :headers="headers"
                    :items="trend_groups"
                    :search="search"
                    :items-per-page="10"
                    multi-sort
                    :footer-props="{
                        showFirstLastPage: true,
                        firstIcon: 'mdi-arrow-collapse-left',
                        lastIcon: 'mdi-arrow-collapse-right',
                        prevIcon: 'mdi-minus',
                        nextIcon: 'mdi-plus'
                      }"
                  >

                    <template v-slot:item.value="{ item }">
                        <v-text-field v-model="item.controller_id" solo></v-text-field>
                    </template>
                    <template v-slot:item.value="{ item }">
                        <v-text-field v-model="item.trend_group_name" solo></v-text-field>
                    </template>
                    <template v-slot:item.value="{ item }">
                        <v-text-field v-model="item.location_name" solo></v-text-field>
                    </template>
                    <template v-slot:item.value="{ item }">
                        <v-text-field v-model="item.update_interval" solo></v-text-field>
                    </template>
                    <template v-slot:item.value="{ item }">
                        <v-text-field v-model="item.query_period" solo></v-text-field>
                    </template>
                </v-data-table>
                </v-card>
              </template>

              <v-row>
                <template>
                    <div class="text-center">
                        <v-dialog v-model="createNew" width="500">
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn color="red lighten-2" dark v-bind="attrs" v-on="on" class="ma-3">Add</v-btn>
                            </template>

                            <v-form :action="createUrl" method="POST" id="create-form">
                                @csrf
                                <v-card>
                                    <v-card-title class="headline grey lighten-2">
                                        Add New TrendGroup
                                    </v-card-title>

                                    <v-text-field name="controller_id" label="Controller ID (in HEX)" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>
                                    <v-text-field name="trend_group_name" label="Trend Group Name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>                                    
{{-- 
                                    <v-select :items="locations" label="Select A Location" name="location_id" item-text="name" item-value="id" solo required>
                                    </v-select> --}}
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
                trend_groups: ( <?php echo json_encode($trend_groups); ?> ),
                headers: [
                    {
                        text: 'Controller ID (in HEX)',
                        align: 'start',
                        value: 'controller_id',
                    },
                    { text: 'Trend group name', value: 'trend_group_name' },
                    { text: 'Location name', value: 'location_name' },
                    { text: 'Update interval', value: 'update_interval' },
                    { text: 'Query period', value: 'query_period' },
                    { text: 'Token / Password', value: 'token' },
                ],              
                search: '',
                createNew: false, 
                createUrl: `${prefix_link}/trendgroup/create`,

            },
            mounted: function() {

            },           
            methods: {
            }
        });
    </script>
@endsection
