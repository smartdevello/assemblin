const stats = [
    {
        number: '42',
        label: 'New leads this week',
    },
    {
        number: '$8,312',
        label: 'Sales this week',
    },
    {
        number: '233',
        label: 'New leads this month',
    },
    {
        number: '$24,748',
        label: 'Sales this month',
    },
]

const tasks = [
    {
        id: 0,
        title: 'Book meeting for Thursday'
    },
    {
        id: 1,
        title: 'Review new leads'
    },
    {
        id: 2,
        title: 'Be awesome!'
    },
]

const newLeads = [
    {
        firstName: 'Giselbert',
        lastName: 'Hartness',
        email: 'ghartness0@mail.ru',
        company: 'Kling LLC',
    },
    {
        firstName: 'Honey',
        lastName: 'Allon',
        email: 'hallon1@epa.gov',
        company: 'Rogahn-Hermann',
    },
    {
        firstName: 'Tommy',
        lastName: 'Rickards',
        email: 'trickards2@timesonline.co.uk',
        company: 'Kreiger, Wehner and Lubowitz',
    },
    {
        firstName: 'Giffy',
        lastName: 'Farquharson',
        email: 'gfarquharson3@goo.gl',
        company: 'Heathcote-Funk',
    },
    {
        firstName: 'Rahel',
        lastName: 'Matches',
        email: 'rmatches4@sfgate.com',
        company: 'Maggio, Russel and Feeney',
    },
    {
        firstName: 'Krystal',
        lastName: 'Natte',
        email: 'knatte5@opera.com',
        company: 'Sanford-Feeney',
    },
    {
        firstName: 'Ronnica',
        lastName: 'Galliver',
        email: 'rgalliver6@epa.gov',
        company: 'Schaefer Group',
    },
    {
        firstName: 'Jenny',
        lastName: 'Bugge',
        email: 'jbugge7@independent.co.uk',
        company: 'Gutmann, Miller and Prosacco',
    },
    {
        firstName: 'Tracee',
        lastName: 'Viscovi',
        email: 'tviscovi8@techcrunch.com',
        company: 'Anderson, Kohler and Renner',
    },
    {
        firstName: 'Teodor',
        lastName: 'Fitzsimmons',
        email: 'tfitzsimmons9@mediafire.com',
        company: 'Durgan-Kovacek',
    },
]
const prefix_link = '/assemblin';
const newLeadsHeaders = [
    {
        text: 'Name',
        value: 'firstName',
    },
    {
        text: 'Email',
        value: 'email',
    },
    {
        text: 'Company',
        value: 'company',
    },
]

const vm = new Vue({
    el: '#app',
    data: {
        drawer: true,
        mainMenu: [
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


            // },
            //
            // people: 'Leads',
            // business: 'Companies',
            // business_center: 'Deals',
            // file_copy: 'Invoices',
            // settings: 'Settings',
        ],
        stats,
        tasks,
        newLeads,
        newLeadsHeaders,
        newLeadsSearch: '',
    },
    methods: {
        clickToggleDrawer() {
            this.drawer = !this.drawer
        },
        clickDeleteTask(task) {
            const i = this.tasks.indexOf(task)
            this.tasks.splice(i, 1)
        },
    },
})
