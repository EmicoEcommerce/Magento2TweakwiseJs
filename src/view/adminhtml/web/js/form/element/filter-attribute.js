define([
    'Magento_Ui/js/form/element/select',
    'jquery',
    'mage/url',
    'ko'
], function (Select, $, urlBuilder, ko) {
    return Select.extend({
        initialize: function () {
            this._super();
            this.initObservable();
            return this;
        },
        initObservable: function () {
            this._super()
                .observe(['categoryId']);

            this.categoryId.subscribe(function (value) {
                this.fetchOptions(value);
            }.bind(this));

            return this;
        },

        fetchOptions: function (categoryId) {
            if (!categoryId) {
                this.options([]);
                return;
            }

            var formKey = $('[name="form_key"]').val();
            var filterTemplate = $('[name="tweakwise_filter_template"]').val();

            $.ajax({
                url: urlBuilder.build('/admin/tweakwise/ajax/facets'),
                type: 'POST',
                data: {
                    form_key: formKey,
                    category_id: categoryId,
                    filter_template: filterTemplate
                },
                success: function (response) {
                    const currentValue = this.value();
                    this.options(response);
                    if (response.some(option => option.value === currentValue)) {
                        this.value(currentValue);
                    }
                }.bind(this)
            });
        }
    });
});
