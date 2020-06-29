(function(){
    var self = lightning.modules.sitemanager = {
        initAdmin: function() {
            $('body').on('click', '.set-configuration', self.setConfig);
        },
        setConfig: function(e) {
            var element = $(e.target);
            var configField = element.data('configuration');
            $.ajax({
                url: '/api/sitemanager/config?field=' + configField,
                type: 'GET',
                dataType: 'html'
            });
        },
        setConfigVal: function(e) {
            var form = $(e).closest('.field-update');
            var field = form.find('input[name=field]').val();
            var value = form.find('input[name=value]').val();
            lightning.dialog.showLoader("Saving ...");
            $.ajax({
                url: '/api/sitemanager/config',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    field: field,
                    value: value,
                    token: lightning.get('token')
                },
                success: function(){
                    lightning.dialog.clear()
                    lightning.dialog.add('Saved', 'message');
                }
            });
        }
    };
})();
