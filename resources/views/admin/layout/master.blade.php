<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.layout.top');
    <link href="{{asset('public/css/test.css')}}" rel="stylesheet">
</head>

<body class="antialiased">


<div id="app">
    <v-app >
        @include('admin.layout.navigation');
        {{--@include('admin.layout.toolbar');--}}
        @yield('content');
    </v-app>
</div>


@include('admin.layout.bottom');
<script>
    var base_url = "{{env('BASE_URL')}}";
</script>
<script>
    const prefix_link = '/assemblin';
    const mainMenu = [
        {
            title: 'Dashboard',
            icon: 'dashboard',
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
            icon: 'settings',
            link: prefix_link  + '/setting'
        }
    ];
</script>
@yield('script');
</body>
</html>
