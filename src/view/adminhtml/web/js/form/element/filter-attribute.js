define([
    'Magento_Ui/js/form/element/select',
    'jquery',
    'mage/url',
    'uiRegistry'
], function (Select, $, urlBuilder, registry) {
    return Select.extend({
        otherFieldName: 'attribute_other',
        otherValue: 'tw_other',
        initialize: function () {
            this._super();

            this.savedValue = this.value();
            this.value('');

            const categoryId = this.source.get('data.category_id');
            this.setOtherFieldVisibility(this.savedValue);

            this.fetchOptions(categoryId).then(function () {
                this.setRestoredValue();
                this.subscribeAttributeValue();
                this.subscribeCategoryId();
            }.bind(this));

            return this;
        },

        setInitialValue: function () {
            return this;
        },

        initObservable: function () {
            this._super().observe(['value']);
            return this;
        },

        subscribeAttributeValue: function () {
            this.value.subscribe(function (newAttributeValue) {
                this.setOtherFieldVisibility(newAttributeValue);
            }.bind(this));
        },

        subscribeCategoryId: function () {
            const categoryIdPath = 'emico_attributelanding_page_form.emico_attributelanding_page_form.general.category_id';
            registry.get(categoryIdPath, (categoryField) => {
                categoryField.value.subscribe((newCategoryId) => {
                    const currentAttributeValue = this.value();
                    this.fetchOptions(newCategoryId).then(() => {
                        this.restoreValue(currentAttributeValue);
                    });
                });
            });
        },

        setOtherFieldVisibility: function (selectedAttribute) {
            registry.get(`${this.parentName}.${this.otherFieldName}`, function (otherField) {
                otherField.disabled(selectedAttribute !== this.otherValue);
            }.bind(this));
        },

        setRestoredValue: function () {
            if (!this.savedValue) {
                return;
            }

            const optionExists = this.options().some(function (option) {
                return option.value === this.savedValue;
            }.bind(this));

            if (optionExists) {
                this.value(this.savedValue);
            }
        },

        restoreValue: function (valueToRestore) {
            const optionExists = this.options().some(function (option) {
                return option.value === valueToRestore;
            }.bind(this));

            if (optionExists) {
                this.value(valueToRestore);
            }
        },

        fetchOptions: function (categoryId) {
            const formKey = $('[name="form_key"]').val();
            const filterTemplate = $('[name="tweakwise_filter_template"]').val();

            return $.ajax({
                url: urlBuilder.build('/admin/tweakwise/ajax/facets'),
                type: 'POST',
                data: {
                    form_key: formKey,
                    category_id: categoryId,
                    filter_template: filterTemplate
                }
            }).done(function (response) {
                this.options(response);
            }.bind(this));
        }
    });
});
