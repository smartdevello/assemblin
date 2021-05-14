<v-navigation-drawer v-model="drawer" app="app" stateless="stateless" floating="floating" width="220">
    <v-toolbar class="brown darken-3">
        <v-list>
            <v-list-item @click="">
                <v-list-item-content>
                    <v-list-item-title class="title">
                        <v-icon >di-cog</v-icon>Assemblin
                    </v-list-item-title>
                </v-list-item-content>
            </v-list-item>
        </v-list>
    </v-toolbar>
    <v-list dense nav>
        <v-list-item v-for="item in mainMenu" :key="item.text" :href="item.link">
            <v-list-item-action>
                <v-icon >@{{ item.icon }}</v-icon>
            </v-list-item-action>
            <v-list-item-content>
                <v-list-item-title>@{{ item.title }}</v-list-item-title>
            </v-list-item-content>
        </v-list-item>
    </v-list>
</v-navigation-drawer>