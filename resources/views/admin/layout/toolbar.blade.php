<v-toolbar class="brown darken-4" app="app">
    <v-toolbar-side-icon @click.stop="clickToggleDrawer"></v-toolbar-side-icon>
    <v-spacer></v-spacer>
    <v-btn icon="icon">
        <v-icon>search</v-icon>
    </v-btn>
    <v-btn icon="icon">
        <v-icon>email</v-icon>
    </v-btn>
    <v-menu offset-y="offset-y">
        <v-btn flat="flat" slot="activator" small="small">John Doe
            <v-icon>keyboard_arrow_down</v-icon>
        </v-btn>
        <v-list>
            <v-list-tile @click="">
                <v-icon class="mr-2">settings</v-icon>
                <v-list-tile-title>Settings</v-list-tile-title>
            </v-list-tile>
            <v-list-tile @click="">
                <v-icon class="mr-2">exit_to_app</v-icon>
                <v-list-tile-title>Logout</v-list-tile-title>
            </v-list-tile>
        </v-list>
    </v-menu>
    <v-avatar class="mr-2" size="36"><img src="http://i.pravatar.cc/150?img=53"/></v-avatar>
</v-toolbar>