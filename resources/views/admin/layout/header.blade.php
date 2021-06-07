<template>
    <v-card height="65px">
      <v-navigation-drawer
        absolute
        permanent
        right
      >
        <template v-slot:prepend>
          <v-list-item two-line>
            <v-list-item-avatar>
              <img src="https://randomuser.me/api/portraits/women/81.jpg">
            </v-list-item-avatar>
  
            <v-list-item-content>
              <v-list-item-title>HKA</v-list-item-title>
              <v-list-item-subtitle>Logged In</v-list-item-subtitle>
            </v-list-item-content>

            <v-list-item-action>
                <v-menu
                    bottom
                    left
                >
                    <template v-slot:activator="{ on, attrs }">
                    <v-btn
                        dark
                        icon
                        v-bind="attrs"
                        v-on="on"
                    >
                        <v-icon       
                            large
                            color="blue-grey darken-2"
                        >mdi-dots-vertical</v-icon>
                    </v-btn>
                    </template>

                    <v-list>
                        <v-list-item>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf            
                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>
                        </v-list-item>
                    </v-list>


                </v-menu>
            </v-list-item-action>
          </v-list-item>
        </template>
  
        <v-divider></v-divider>
      </v-navigation-drawer>
    </v-card>
  </template>
