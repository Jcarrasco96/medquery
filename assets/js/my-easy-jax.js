window.easyJax = (function ($) {

    "use strict";

    const methods = {

        /**
         * @param {Object} params
         * @param {string} params.title
         * @param {string} params.content
         * @param {string} params.size
         */
        modal: function (params) {
            let modal = $('#modal-app');

            if (!modal) {
                console.log('Modal not found!');
                return;
            }

            if (typeof params.title !== "undefined") {
                modal.find('#modal-app-title').html(params.title);
            }

            if (typeof params.content !== "undefined") {
                modal.find('#modal-app-container').html(params.content);
            }

            modal.find(".modal-dialog").removeClass(['modal-sm', 'modal-lg', 'modal-xl'])

            if (typeof params.size !== "undefined") {
                modal.find(".modal-dialog").addClass(params.size);
            }

            modal.modal('show');
        },

        modal_close: function () {
            let modal = $('#modal-app');

            if (!modal) {
                console.log('Modal not found!');
                return;
            }

            modal.modal('hide');
        },

        /**
         * @param {Object} params
         * @param {string} params.message
         * @param {string} params.type
         * @param {string} params.icon
         */
        notify: function (params) {
            notify(params.message, params.type, params.icon);
        },

        /**
         * @param {Object} params
         * @param {boolean} params.ajax
         * @param {string} params.url
         * @param {boolean} params.processResponse
         */
        redirect: function (params) {
            if (params.ajax === true) {
                $.get(params.url, function (res) {
                    if (params.processResponse === true) {
                        this.response(res);
                    }
                });
                return;
            }

            window.location.href = params.url;
        },

        /**
         * @param {string[]} params
         */
        form_reset: function (params) {
            $.each(params, function (index, formId) {
                $(formId).trigger('reset');
            });
        },

        reload_page: function () {
            window.location.reload();
        },

        /**
         * @param {any[]} params
         */
        content_replace: function (params) {
            $.each(params, function (tagId, tagContent) {
                $(tagId).html(tagContent);
            });
        },

        content_remove: function (id) {
            $('#' + id).remove();
        },

        open_window: function (url) {
            window.open(url, '_blank');
        }

    };

    /**
     * @param value
     * @returns {*|number|boolean}
     */
    function cast(value) {
        if (value === 'true') {
            return true;
        }

        if (value === 'false') {
            return false;
        }

        if (!isNaN(value)) {
            return Number(value);
        }

        return value;
    }

    const pub = {

        /**
         * @param {Element} element
         * @returns {{method: string, data: *[], target: string}}
         */
        requestor: function (element) {
            const object = {
                method: 'get', data: [], target: '',
            };

            if (element.hasAttribute("data-jax-method")) {
                object.method = element.getAttribute("data-jax-method");
            }

            if (element.hasAttribute("data-href")) {
                object.target = element.getAttribute("data-href");
            } else {
                object.target = element.getAttribute("href");
            }

            let dataAux = [];
            $.each(element.attributes, function () {
                if (this.name.startsWith('data-jax-data-')) {
                    const key = this.name.replace('data-jax-data-', '');
                    dataAux.push({name: key, value: cast(this.value)});
                }
            });

            let form_id = null;

            if (element.hasAttribute("data-form-id")) {
                form_id = element.getAttribute("data-form-id");
            } else if (element.closest('#modal-app') !== null && document.getElementById('modal-app').getElementsByTagName('form').length > 0) {
                form_id = document.getElementById('modal-app').getElementsByTagName('form')[0].getAttribute('id');
            }

            if (form_id !== null && form_id !== false && form_id !== 'skip_form') {
                const form = $("#" + form_id);
                if (form.attr('method') !== null) {
                    object.method = form.attr('method');
                }
                object.target = form.attr('action');
                object.data = form.serializeArray();
                object.data.push({name: "jax-submit", value: true});
                object.data.push({name: "form-id", value: form_id});
                object.data.push(...dataAux);

                let hasFile = form.find('input[type="file"]').toArray().some(i => i.files && i.files.length > 0);

                if (hasFile) {
                    object.data = new FormData(form[0]);
                    object.data.append("jax-submit", true);
                    object.data.append("form-id", form_id);
                    dataAux.forEach(item => {
                        object.data.append(item.name, item.value);
                    });
                }
            } else {
                object.data.push(...dataAux);
            }

            return object;
        },

        /**
         * @param {any[]} data
         */
        response: function (data) {
            if (!data) {
                return;
            }

            $.each(data, function (key, value) {
                if (typeof value !== "object") {
                    return;
                }

                $.each(value, function (method, params) {
                    if (typeof methods[method] !== "function") {
                        return;
                    }

                    methods[method](params);
                });
            });
        },

        /**
         * @param {Element} element
         * @//param {function} callback, callback = () => {}
         */
        request: function (element) {
            const req_params = pub.requestor(element);

            let ajaxOptions = {
                url: req_params.target, type: req_params.method, data: req_params.data, dataType: 'json',
            };

            if (req_params.data instanceof FormData) {
                ajaxOptions.processData = false;
                ajaxOptions.contentType = false;
            }

            $.ajax(ajaxOptions)
                .done(pub.response)
                .fail(function (jqXHR, textStatus) {
                    console.log(textStatus);
                });
            // .always(function () {
            //     callback();
            // });
        },

    };

    return pub;

})(window.jQuery);

jQuery(document).ready(function () {

    jQuery(document).on("click", "[data-jax='1']", function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        // const originalHtml = jQuery(this).html();

        // jQuery(this).prop('disabled', true);
        // jQuery(this).html('<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span role="status">Loading...</span>');

        // const resetButton = function () {
        //     jQuery(this).prop('disabled', false).html(originalHtml);
        // }

        if (jQuery(this)[0].hasAttribute("data-jax-confirm")) {
            if (window.confirm(jQuery(this).attr("data-jax-confirm"))) {
                easyJax.request(this);
            }
        } else {
            easyJax.request(this);
        }
    });

});
