<v-navigation-drawer v-model="drawer" app="app" stateless="stateless" floating="floating" width="220">
    <v-toolbar class="brown darken-3">
        <v-list>
            <v-list-tile @click="">
                <v-list-tile-content>
                    <v-list-tile-title class="title">
                        <v-icon class="mr-2">data_usage</v-icon>Assemblin
                    </v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
    </v-toolbar>
    <v-list dense nav>
        <v-list-tile v-for="item in mainMenu" :key="item.text" :href="item.link">
            <v-list-tile-action>
                <v-icon >@{{ item.icon }}</v-icon>
            </v-list-tile-action>
            <v-list-tile-content>
                <v-list-tile-title>@{{ item.title }}</v-list-tile-title>
            </v-list-tile-content>
        </v-list-tile>
    </v-list>
</v-navigation-drawer>