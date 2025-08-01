define([
    'Magento_Ui/js/form/element/select',
    'jquery',
    'mage/url',
    'uiRegistry'
], function (Select, $, urlBuilder, registry) {
    return Select.extend({
        attributeFieldName: 'attribute',
        otherFieldName: 'attribute_other',
        otherValue: 'tw_other',
        initialize: function () {
            this._super();
            this.savedValue = this.value();
            return this;
        },

        setInitialValue: function () {
            return this;
        },

        initFromAttribute: function (attribute) {
            const currentValue = this.value() ? this.value() : this.savedValue;
            this.fetchOptions(attribute).then(() => {
                console.log('SETTING VALUE ' + currentValue);
                this.restoreValue(currentValue);
            });
        },

        // setOtherFieldVisibility: function (selectedAttribute) {
        //     registry.get(`${this.parentName}.${this.otherFieldName}`, function (otherField) {
        //         otherField.disabled(selectedAttribute !== this.otherValue);
        //     }.bind(this));
        // },

        restoreValue: function (valueToRestore) {
            const optionExists = this.options().some(function (option) {
                return option.value === valueToRestore;
            }.bind(this));

            if (optionExists) {
                this.value(valueToRestore);
            } else {
                const firstOption = this.options()[0];
                if(firstOption) {
                    this.value(firstOption.value);
                }
            }
        },

        fetchOptions: function (attribute) {
            const formKey = $('[name="form_key"]').val();
            const filterTemplate = this.source.get('data.tweakwise_filter_template');
            const categoryId = this.source.get('data.category_id');

            return $.ajax({
                url: urlBuilder.build('/admin/tweakwise/ajax/facetattributes'),
                type: 'POST',
                data: {
                    form_key: formKey,
                    category_id: categoryId,
                    filter_template: filterTemplate,
                    facet_key: attribute
                }
            }).done(function (response) {
                this.options(response);
            }.bind(this));
        }
    });
});
