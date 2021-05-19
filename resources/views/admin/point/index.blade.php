@extends('admin.layout.master')
@section('content')
    <v-main >
        <v-container>
            <v-row>
                <v-card v-for="point in points" :key="point.id"  width="300" elevation="10" class="ma-2">
                    <v-card-title>@{{ point . label }}</v-card-title>
                    <v-card-title>@{{ point . name }}</v-card-title>
                </v-card>
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
