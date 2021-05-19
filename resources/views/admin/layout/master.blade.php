<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.layout.top')

    @yield('style')
    <style>
        [v-cloak]{
            display: none;
        }
    </style>
</head>

<body class="antialiased">

    <div id="app">
        <div class="v-cloak">    
            <v-app >
                @include('admin.layout.navigation')
                {{--@include('admin.layout.toolbar');--}}
                @yield('content')
            </v-app>
        </div>
    </div>


@include('admin.layout.bottom')
<script>
    var base_url = "{{config()->get('constants.base_url')}}";
</script>
<script>
    const prefix_link = "{{config()->get('constants.prefix_url')}}";
    const mainMenu = [
        {
            title: 'Dashboard',
            icon: 'mdi-view-dashboard',
            link: prefix_link  + '/'
        },
        {
            title: 'Location',
            icon: 'mdi-map-marker-radius',
            link: prefix_link  + '/location'

        },
        {
            title: 'Building',
            icon: 'mdi-office-building',
            link: prefix_link  + '/building'
        },
        {
            title: 'Area',
            icon: 'mdi-floor-plan',
            link: prefix_link  + '/area'
        },
        {
            title: 'Controller',
            icon: 'mdi-lan',
            link: prefix_link  + '/controller'
        },
        {
            title: 'Settings',
            icon: 'mdi-cog',
            link: prefix_link  + '/setting'
        }
    ];
</script>
@yield('script')
</body>
</html>
