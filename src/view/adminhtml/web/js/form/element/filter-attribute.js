define([
    'Magento_Ui/js/form/element/select',
    'uiRegistry',
    'jquery',
    'mage/url'
], function (Select, registry, $, urlBuilder) {
    return Select.extend({
        otherFieldName: 'attribute_other',
        otherValue: 'tw_other',
        initialize: function () {
            this._super();
            this.subscribeCategoryId();
            return this;
        },

        initObservable: function () {
            this._super().observe(['value']);
            this.value.subscribe(function (newValue) {
                this.setSelectedValue(newValue);
                this.setOtherFieldVisibility();
            }.bind(this));

            return this;
        },

        setInitialValue: function () {
          return this;
        },

        subscribeCategoryId: function () {
            const categoryIdPath = 'emico_attributelanding_page_form.emico_attributelanding_page_form.general.category_id';
            registry.get(categoryIdPath, function (categoryField) {
                this.fetchOptions(categoryField.value());

                categoryField.value.subscribe(this.fetchOptions.bind(this));
            }.bind(this));
        },

        setSelectedValue: function (value) {
            if (value !== undefined) {
                this.selectedValue = value;
                return;
            }

            this.value(this.selectedValue);
        },

        setOtherFieldVisibility: function () {
            registry.get(`${this.parentName}.${this.otherFieldName}`, function (otherField) {
                otherField.disabled(this.selectedValue !== this.otherValue);
            }.bind(this));
        },

        fetchOptions: function (categoryId) {
            var formKey = $('[name="form_key"]').val();
            var filterTemplate = $('[name="tweakwise_filter_template"]').val();
            const currentValue = this.value();

            $.ajax({
                url: urlBuilder.build('/admin/tweakwise/ajax/facets'),
                type: 'POST',
                data: {
                    form_key: formKey,
                    category_id: categoryId,
                    filter_template: filterTemplate
                },
                success: function (response) {
                    this.options(response);
                    if (response.some(option => option.value === currentValue)) {
                        this.value(currentValue);
                    }
                }.bind(this)
            });
        }
    });
});
