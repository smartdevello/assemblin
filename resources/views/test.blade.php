@extends('admin.layout.master');
@section('content');
<v-content>
    <v-container class="pa-4" fluid="fluid" grid-list-md="grid-list-md">
        <v-layout wrap="wrap">
            <v-flex xs12="xs12">
                <h1 class="display-1 mb-1">Dashboard</h1>
            </v-flex>
            <v-flex xs12="xs12" md6="md6">
                <v-layout wrap="wrap">
                    <v-flex v-for="stat in stats" xs6="xs6">
                        <v-card class="text-xs-center" height="100%">
                            <v-card-text>
                                <div class="display-1 mb-2">@{{ stat.number }}</div>@{{ stat.label }}
                            </v-card-text>
                        </v-card>
                    </v-flex>
                </v-layout>
            </v-flex>
            <v-flex md6="md6">
                <v-card height="100%">
                    <v-card-title class="grey darken-4">Tasks</v-card-title>
                    <v-data-table class="mt-1" :items="tasks" hide-headers="hide-headers" hide-actions="hide-actions">
                        <template slot="items" slot-scope="props">
                            <td>
                                <v-checkbox @click="clickDeleteTask(props.item)"></v-checkbox>
                            </td>
                            <td>@{{ props.item.title }}</td>
                        </template>
                    </v-data-table>
                </v-card>
            </v-flex>
            <v-flex xs12="xs12">
                <v-card>
                    <v-card-title class="grey darken-4">New Leads
                        <v-spacer></v-spacer>
                        <v-text-field v-model="newLeadsSearch" append-icon="search" label="Search"></v-text-field>
                    </v-card-title>
                    <v-data-table :headers="newLeadsHeaders" :items="newLeads" :search="newLeadsSearch">
                        <template slot="items" slot-scope="props">
                            <td>@{{ props.item.firstName }} @{{ props.item.lastName }}</td>
                            <td>@{{ props.item.email }}</td>
                            <td>@{{ props.item.company }}</td>
                        </template>
                    </v-data-table>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
</v-content>
@endsection