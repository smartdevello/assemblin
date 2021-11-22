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
                    :loading="loading" loading-text="Loading... Please wait"                    
                    multi-sort
                    :footer-props="{
                        showFirstLastPage: true,
                        firstIcon: 'mdi-arrow-collapse-left',
                        lastIcon: 'mdi-arrow-collapse-right',
                        prevIcon: 'mdi-minus',
                        nextIcon: 'mdi-plus'
                      }"
                  >

                    <template v-slot:item.controller_id="{ item }">
                        <v-text-field v-model="item.controller_id" solo></v-text-field>
                    </template>                   
                    <template v-slot:item.trend_group_name="{ item }">
                        <v-text-field v-model="item.trend_group_name" solo></v-text-field>
                    </template>
                    <template v-slot:item.location_name="{ item }">
                        <v-text-field v-model="item.location_name" solo></v-text-field>
                    </template>
                    <template v-slot:item.update_interval="{ item }">
                        <v-text-field v-model="item.update_interval" solo></v-text-field>
                    </template>
                    <template v-slot:item.query_period="{ item }">
                        <v-text-field v-model="item.query_period" solo></v-text-field>
                    </template>
                    <template v-slot:item.send_to_ftp="{ item }">
                        <v-checkbox v-model="item.send_to_ftp"></v-checkbox>
                    </template>

                    <template v-slot:item.action="{ item }">
                        <v-btn color="success" @click="updateItem(item)" :loading="item.updateloading" :disabled="loading">Update</v-btn>
                        <v-btn color="error" @click="deleteItem(item)" :loading="item.deleteloading" :disabled="loading">Delete</v-btn>                        
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
                                    <v-text-field name="trend_group_name" label="Trend group name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>                                    
                                    <v-text-field name="location_name" label="Location name" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>                                    
                                    <v-text-field name="update_interval" label="Update interval" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>                                    
                                    <v-text-field name="query_period" label="Query period" required class="pa-2" :rules="[ v => !!v || 'Field is required', ]"></v-text-field>                                    
                                    <v-checkbox v-model="send_to_ftp" label="Send CSV to FTP?" ></v-checkbox>
                                    <input type="hidden" name="send_to_ftp" :value="send_to_ftp">
                                    
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
                loading: false,
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
                    { text: 'Send To FTP', value: 'send_to_ftp' },
                    { text: "Action", value: "action", sortable: false, align : 'center'}
                ],              
                search: '',
                createNew: false, 
                send_to_ftp : 0,
                createUrl: `${prefix_link}/trendgroup/create`,

            },
            mounted: function() {
                // axios.get('https://reqres.in/api/users').then(response => {
                //     console.log(response);
                // });
                // axios.post('https://reqres.in/api/register', {
                //     email : 'michael.s22@outlook.com',
                //     password: 'pistol'
                // }).then(response => {
                //     console.log(response);
                // }).catch(err => {

                // });
            },           
            methods: {
                updateItem(item){
                    this.loading = true;
                    item.updateloading = true;

                    let url = '/trendgroup/update/' + item.id;
                    axios.post(url, item).then(response=>{
                        this.loading = false;
                        item.updateloading = false;
                        console.log(response);
                        toastr.options = {
                                "closeButton": false,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": false,
                                "positionClass": "toast-bottom-center",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": "300",
                                "hideDuration": "1000",
                                "timeOut": "5000",
                                "extendedTimeOut": "1000",
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            };
                        toastr.success('Updated Successfully');

                    }).catch(err => {
                        this.loading = false;
                        item.updateloading = false;
                        if (err.response){
                            data = err.response.data;
                            msg = '';
                            for (key in data.errors) {
                                if (Array.isArray ( data.errors[key] )) {
                                    data.errors[key].forEach(element => {
                                        msg += element + "\n";
                                    });
                                }
                            }
                            console.log(data.errors);
                            toastr.error(msg);
                        }
                    });

                },
                deleteItem(item){
                    this.loading = true;
                    item.deleteloading = true;

                    let url = '/trendgroup/delete/' + item.id;
                    axios.post(url).then(response=>{
                        this.loading = false;
                        item.updateloading = false;
                        for ( i = this.trend_groups.length - 1; i>=0 ;i--){                            
                            if (this.trend_groups[i].id == item.id) {
                                this.trend_groups.splice(i, 1);
                                break;
                            }                                
                        }
                    }).catch(err => {
                        this.loading = false;
                        item.updateloading = false;
                        console.log(err);
                    });

                }
            }
        });
    </script>
@endsection
