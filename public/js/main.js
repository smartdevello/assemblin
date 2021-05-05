$(document).ready(function(){

var main_vm = new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    data: {
        devices: null,
        DEOSPoints: null,
        DEOSvalue: '',
    },

    mounted: function()
    {
        this.getFoxeriotDevices();
        this.getDEOSPoints();

        // this.reload_template_products();
        // client.get('ticket.requester.email').then(function(data) {
        //     main_vm.coupon.allowed_emails = data["ticket.requester.email"];
        // });
    },
    watch: {
        devices: function(){

        }
    },
    methods: {
        getFoxeriotDevices: function(){

            $.ajax({
                url: "http://hkasrv4.hameenkiinteistoautomaatio.fi/api/foxeriot/devices",
                success: function(data)
                {
                    main_vm.devices = JSON.parse(data);
                }
            });
        },
        getDEOSPoints: function(){
            $.ajax({
                url: "http://hkasrv4.hameenkiinteistoautomaatio.fi/api/points",
                success: function(data)
                {
                    main_vm.DEOSPoints = data;
                    // console.log(data);
                }
            });
        },
        create_template: function() {
            this.is_creating_template = true;
            $.ajax({
                url: "https://kiinde.com/wp-json/kiinde/v1/coupon_template",
                type: "POST",
                data: this.template,
                complete: function () {
                    main_vm.is_creating_template = false;
                    main_vm.reload_template_products();
                }
            });
        },
        reload_template_products: function(){
            $.ajax({
                url: "https://kiinde.com/wp-json/kiinde/v1/coupon_template",
                success: function(data)
                {
                    main_vm.all_templates = data
                }
            }),

            $.ajax({
                url: "https://kiinde.com/wp-json/wc/v3/products?per_page=100",
                headers: {
                    "Authorization": "Basic " + btoa("ck_455b248c68f2e83b642f3d6d12807a10dad357de:cs_449aa1e07f551ebd62d43e452ff4b4c7e0646188")
                },
                success: function(data)
                {
                    main_vm.products = data.filter(x => x.type == "simple" );
                    let variable_products = data.filter(x => x.type == "variable" );
                    for(var i=0; i < variable_products.length; i++)
                    {
                        var product_name = variable_products[i].name;
                        if(product_name.length > 15)
                        {
                            product_name = product_name.slice(0, 20) + "...";
                        }
                        $.ajax({
                            url: "https://kiinde.com/wp-json/wc/v3/products/" + variable_products[i].id + "/variations",
                            headers: {
                                "Authorization": "Basic " + btoa("ck_455b248c68f2e83b642f3d6d12807a10dad357de:cs_449aa1e07f551ebd62d43e452ff4b4c7e0646188")
                            },
                            success: function(result)
                            {
                                main_vm.products = main_vm.products.concat(result);
                            }
                        });
                    }
                }
            });
        },
        change_template: function() {
            this.coupon.discount_type = this.selected_template.discount_type;
            this.coupon.coupon_amount = this.selected_template.coupon_amount;
            this.coupon.coupon_expiry = this.selected_template.coupon_expiry;
            this.coupon.grant_free_shipping = (this.selected_template.grant_free_shipping == "true")? true: false;
            this.coupon.accounting_expense_account = this.selected_template.accounting_expense_account;
            this.coupon.products = this.selected_template.products.map(x => parseInt(x));
            this.coupon.usage_limit = this.selected_template.usage_limit;
            this.coupon.limit_usage_to_x_items = this.selected_template.limit_usage_to_x_items;
            this.coupon.usage_limit_per_user = this.selected_template.usage_limit_per_user;
            this.coupon.code = Math.random().toString(36).substring(2, 15);
        },

        check_exist_coupon: function(){
            // console.log('coupon', this.coupon);
            this.is_creating_coupon = true;
            $.ajax({
                url: "https://kiinde.com/wp-json/kiinde/v1/coupon_exists",
                type: "POST",
                headers: {
                    "Authorization": "Basic " + btoa("ck_455b248c68f2e83b642f3d6d12807a10dad357de:cs_449aa1e07f551ebd62d43e452ff4b4c7e0646188")
                },
                data: {
                    "email": this.coupon.allowed_emails,
                    "products": this.coupon.products
                },
                success: function(result)
                {
                    if(result.exists)
                    {
                        console.log("Coupon has already been exist!");
                        toastr.warning("Coupon has already been exist!");


                    } else{
                        main_vm.create_coupon();
                        console.log("Success create Coupon!")
                    }

                },
                complete: function()
                {
                    main_vm.is_creating_coupon = false;

                }
            });
        },


        create_coupon: function() {
            this.is_creating_coupon = true;
            var expire = new Date();
            expire.setDate(expire.getDate() + parseInt(this.coupon.coupon_expiry));
            $.ajax({
                url: "https://kiinde.com/wp-json/wc/v3/coupons",
                type: "POST",
                headers: {
                    "Authorization": "Basic " + btoa("ck_455b248c68f2e83b642f3d6d12807a10dad357de:cs_449aa1e07f551ebd62d43e452ff4b4c7e0646188")
                },
                data: {
                    "code" : this.coupon.code,
                    "amount": this.coupon.coupon_amount,
                    "discount_type": this.coupon.discount_type,
                    "date_expires": expire.toISOString(),
                    "product_ids" : this.coupon.products,
                    "free_shipping" : this.coupon.grant_free_shipping,
                    "email_restrictions" : this.coupon.allowed_emails,
                    "usage_limit" : this.coupon.usage_limit,
                    "limit_usage_to_x_items" : this.coupon.limit_usage_to_x_items,
                    "usage_limit_per_user" : this.coupon.usage_limit_per_user,
                    "meta_data" : [
                        {
                            "key" : "kiinde_accounting_expense_account",
                            "value" : this.coupon.accounting_expense_account
                        },
                        {
                            "key" : "_wc_url_coupons_unique_url",
                            "value" : "promo/" + this.coupon.code,
                        },
                        {
                            "key" : "_wc_url_coupons_redirect_page",
                            "value" : 42,
                        },
                        {
                            "key" : "_wc_url_coupons_redirect_page_type",
                            "value" : "page",
                        },
                        {
                            "key" : "_wc_url_coupons_product_ids",
                            "value" : this.coupon.products,
                        },
                        {
                            "key" : "usage_limit",
                            "value" : this.coupon.usage_limit,
                        },
                        {
                            "key" : "limit_usage_to_x_items",
                            "value" : this.coupon.limit_usage_to_x_items,
                        },
                        {
                            "key" : "usage_limit_per_user",
                            "value" : this.coupon.usage_limit_per_user,
                        },

                    ]
                },
                complete: function () {
                    toastr.success('Coupon has been created');
                    main_vm.is_creating_coupon = false;
                    main_vm.reset_coupon_fields();
                }
            });
        },

        reset_coupon_fields: function() {
            this.selected_template = null;
            this.coupon.discount_type = "percent";
            this.coupon.coupon_amount = 0;
            this.coupon.coupon_expiry = 0;
            this.coupon.grant_free_shipping = false;
            this.coupon.accounting_expense_account = "";
            this.coupon.products = [];
            this.coupon.usage_limit = 1;
            this.coupon.limit_usage_to_x_items = 1;
            this.coupon.usage_limit_per_user = 1;
            this.coupon.code = Math.random().toString(36).substring(2, 15);
        },

        copyUniqueURL: function () {
            let txtUniqueUrl = this.$refs.txtUniqueUrl.$el.querySelector('input')
            txtUniqueUrl.select()
            document.execCommand("copy");
        }
    },

    computed: {
        // unique_url: function() {
        //     return "https://kiinde.com/promo/" + this.coupon.code + "/a";
        // }
    }
});
});
